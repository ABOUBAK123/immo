<?php

namespace App\Http\Controllers;

use App\Models\NotificationEnvoyee;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, ['admin', 'proprietaire']), 403);

        $query = NotificationEnvoyee::where('proprietaire_id', $user->id)
            ->with('locataire', 'paiement');

        if ($request->filled('canal')) {
            $query->where('canal', $request->canal);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $query->whereHas('locataire', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        // Locataires du propriétaire
        $locataires = $this->getLocataires($user);

        // Paiements en retard pour les boutons rapides
        $retardataires = Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
            ->where('statut', 'en_attente')
            ->where('date_echeance', '<', now())
            ->with('location.locataire', 'location.bien')
            ->get()
            ->unique('location.locataire_id');

        // Stats du mois
        $stats = [
            'total_mois'  => NotificationEnvoyee::where('proprietaire_id', $user->id)
                ->whereMonth('created_at', now()->month)->count(),
            'par_email'   => NotificationEnvoyee::where('proprietaire_id', $user->id)->where('canal', 'email')->count(),
            'par_sms'     => NotificationEnvoyee::where('proprietaire_id', $user->id)->where('canal', 'sms')->count(),
            'par_whatsapp'=> NotificationEnvoyee::where('proprietaire_id', $user->id)->where('canal', 'whatsapp')->count(),
            'retardataires' => $retardataires->count(),
        ];

        return view('notifications.index', compact('notifications', 'locataires', 'retardataires', 'stats'));
    }

    public function send(Request $request)
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, ['admin', 'proprietaire']), 403);

        $data = $request->validate([
            'locataire_ids'  => 'required|array|min:1',
            'locataire_ids.*'=> 'exists:users,id',
            'canal'          => 'required|in:email,sms,whatsapp',
            'type'           => 'required|in:alerte_loyer,relance_retard,quittance,personnalise',
            'sujet'          => 'nullable|string|max:191',
            'message'        => 'required|string|max:2000',
        ]);

        $envoyes = 0;
        $erreurs = 0;

        foreach ($data['locataire_ids'] as $locataireId) {
            $locataire = User::findOrFail($locataireId);
            $contact   = $data['canal'] === 'email' ? $locataire->email : ($locataire->phone ?? '');
            $message   = $this->interpoler($data['message'], $locataire, $user);
            $statut    = 'simule';
            $erreur    = null;

            // ── Envoi réel Email ─────────────────────────────────────────────
            if ($data['canal'] === 'email' && $locataire->email) {
                try {
                    Mail::raw($message, function ($mail) use ($locataire, $user, $data) {
                        $mail->to($locataire->email, $locataire->name)
                             ->from(config('mail.from.address', 'noreply@immogest.fr'), $user->name)
                             ->subject($data['sujet'] ?? NotificationEnvoyee::typeLabel($data['type']));
                    });
                    $statut = 'envoye';
                    $envoyes++;
                } catch (\Exception $e) {
                    $statut = 'echec';
                    $erreur = $e->getMessage();
                    $erreurs++;
                }
            } elseif (in_array($data['canal'], ['sms', 'whatsapp'])) {
                // SMS / WhatsApp : intégration Twilio, Vonage, etc.
                // Pour activer : configurer les clés API dans .env et décommenter ci-dessous.
                // $statut = $this->envoyerViaTwilio($data['canal'], $contact, $message);
                $statut = 'simule'; // mode démo
                $envoyes++;
            } else {
                $statut = 'simule';
                $envoyes++;
            }

            NotificationEnvoyee::create([
                'proprietaire_id'     => $user->id,
                'locataire_id'        => $locataireId,
                'canal'               => $data['canal'],
                'type'                => $data['type'],
                'sujet'               => $data['sujet'],
                'message'             => $message,
                'destinataire_contact'=> $contact,
                'statut'              => $statut,
                'erreur'              => $erreur,
                'sent_at'             => now(),
            ]);
        }

        $msg = "$envoyes notification(s) envoyée(s)";
        if ($erreurs) $msg .= " ($erreurs erreur(s))";
        if ($data['canal'] !== 'email') $msg .= ' — Mode démo (configurez Twilio pour SMS/WhatsApp réel)';

        return back()->with('success', $msg);
    }

    public function sendBulkRetards(Request $request)
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, ['admin', 'proprietaire']), 403);

        $canal = $request->input('canal', 'email');

        $retardataires = Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
            ->where('statut', 'en_attente')
            ->where('date_echeance', '<', now())
            ->with('location.locataire', 'location.bien')
            ->get()
            ->unique('location.locataire_id');

        $envoyes = 0;
        foreach ($retardataires as $paiement) {
            $locataire = $paiement->location->locataire;
            $template  = $this->templateRetard($locataire, $paiement, $user);
            $contact   = $canal === 'email' ? $locataire->email : ($locataire->phone ?? '');

            $statut = 'simule';
            if ($canal === 'email' && $locataire->email) {
                try {
                    Mail::raw($template, function ($mail) use ($locataire, $user) {
                        $mail->to($locataire->email, $locataire->name)
                             ->from(config('mail.from.address', 'noreply@immogest.fr'), $user->name)
                             ->subject('⚠️ Loyer en retard — Relance amiable');
                    });
                    $statut = 'envoye';
                } catch (\Exception $e) {
                    $statut = 'echec';
                }
            }

            NotificationEnvoyee::create([
                'proprietaire_id'     => $user->id,
                'locataire_id'        => $locataire->id,
                'paiement_id'         => $paiement->id,
                'canal'               => $canal,
                'type'                => 'relance_retard',
                'sujet'               => 'Loyer en retard — Relance amiable',
                'message'             => $template,
                'destinataire_contact'=> $contact,
                'statut'              => $statut,
                'sent_at'             => now(),
            ]);
            $envoyes++;
        }

        return back()->with('success', "$envoyes relance(s) envoyée(s) aux locataires en retard.");
    }

    // ─── Templates ────────────────────────────────────────────────────────────
    private function interpoler(string $message, User $locataire, User $proprietaire): string
    {
        $prenom = explode(' ', $locataire->name)[0];
        return str_replace(
            ['[Prénom]', '[Nom]', '[Propriétaire]'],
            [$prenom, $locataire->name, $proprietaire->name],
            $message
        );
    }

    private function templateRetard(User $locataire, Paiement $paiement, User $proprietaire): string
    {
        $prenom  = explode(' ', $locataire->name)[0];
        $montant = number_format($paiement->montant, 0, ',', ' ');
        $date    = $paiement->date_echeance->format('d/m/Y');
        $bien    = $paiement->location->bien->titre ?? '';

        return "Bonjour {$prenom},\n\nNous vous contactons concernant votre loyer de {$montant} € relatif au logement « {$bien} », "
             . "dont le paiement était attendu le {$date}.\n\n"
             . "À ce jour, nous n'avons pas reçu ce règlement. Nous vous invitons à procéder au paiement dans les meilleurs délais "
             . "afin d'éviter toute procédure de recouvrement.\n\n"
             . "En cas de difficulté, n'hésitez pas à nous contacter pour convenir d'un arrangement.\n\n"
             . "Cordialement,\n{$proprietaire->name}";
    }

    // ─── Cloche in-app ────────────────────────────────────────────────────────

    public function bell(Request $request)
    {
        $user  = Auth::user();
        $notifs = $user->unreadNotifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($n) => [
                'id'      => $n->id,
                'titre'   => $n->data['titre'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'icone'   => $n->data['icone'] ?? 'bi-bell',
                'couleur' => $n->data['couleur'] ?? '#EA580C',
                'url'     => $n->data['url'] ?? '#',
                'temps'   => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count'  => $user->unreadNotifications()->count(),
            'items'  => $notifs,
        ]);
    }

    public function marquerLue(Request $request, string $id)
    {
        $notif = Auth::user()->notifications()->find($id);
        if ($notif) {
            $notif->markAsRead();
        }
        return response()->json(['ok' => true]);
    }

    public function lireTout(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    private function getLocataires(User $user): \Illuminate\Support\Collection
    {
        return User::where('role', 'locataire')
            ->whereHas('locations.bien', fn($q) => $q->where('proprietaire_id', $user->id))
            ->with(['locations' => fn($q) => $q->where('statut', 'actif')->with('bien')->limit(1)])
            ->orderBy('name')
            ->get();
    }
}
