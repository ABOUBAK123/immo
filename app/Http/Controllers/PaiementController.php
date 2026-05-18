<?php

namespace App\Http\Controllers;

use App\Mail\QuittanceMail;
use App\Services\RelanceIaService;
use App\Models\Bien;
use App\Models\Intervention;
use App\Models\Location;
use App\Models\Paiement;
use App\Models\Parametre;
use App\Models\Quittance;
use App\Models\User;
use App\Notifications\PaiementRecuNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        $user        = Auth::user();
        $statut      = $request->statut;
        $bienId      = $request->bien_id;
        $residence   = $request->residence;
        $locataireId = $request->locataire_id;

        $query = match ($user->role) {
            'admin'        => Paiement::with('location.bien', 'location.locataire', 'quittance'),
            'proprietaire' => Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
                ->with('location.bien', 'location.locataire', 'quittance'),
            'locataire'    => Paiement::whereHas('location', fn($q) => $q->where('locataire_id', $user->id))
                ->with('location.bien', 'quittance'),
            default        => Paiement::whereRaw('1=0'),
        };

        if ($statut === 'en_retard') {
            $query->where('statut', 'en_attente')->where('date_echeance', '<', now());
        } elseif ($statut) {
            $query->where('statut', $statut);
        }

        if ($bienId) {
            $query->whereHas('location', fn($q) => $q->where('bien_id', $bienId));
        }

        if ($residence) {
            $query->whereHas('location.bien', fn($q) => $q->where('nom_residence', $residence));
        }

        if ($locataireId && $user->role !== 'locataire') {
            $query->whereHas('location', fn($q) => $q->where('locataire_id', $locataireId));
        }

        // Options pour les filtres (scopées au rôle)
        [$biens, $residences, $locataires] = match ($user->role) {
            'admin' => [
                Bien::orderBy('titre')->get(['id', 'titre', 'nom_residence']),
                Bien::whereNotNull('nom_residence')->distinct()->orderBy('nom_residence')->pluck('nom_residence'),
                User::where('role', 'locataire')->orderBy('name')->get(['id', 'name']),
            ],
            'proprietaire' => [
                Bien::where('proprietaire_id', $user->id)->orderBy('titre')->get(['id', 'titre', 'nom_residence']),
                Bien::where('proprietaire_id', $user->id)->whereNotNull('nom_residence')->distinct()->orderBy('nom_residence')->pluck('nom_residence'),
                User::whereIn('id', Location::whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))->pluck('locataire_id'))->orderBy('name')->get(['id', 'name']),
            ],
            default => [collect(), collect(), collect()],
        };

        // Récupérer le dernier paiement payé indépendamment des filtres actifs :
        // - Pour le locataire connecté : sa propre quittance (toujours visible)
        // - Pour admin/proprio filtrant par locataire_id : permet d'afficher la bannière de confirmation
        $dernierPaiementPaye = null;
        if ($user->role === 'locataire') {
            $dernierPaiementPaye = Paiement::whereHas('location', fn($q) => $q->where('locataire_id', $user->id))
                ->with('location.bien', 'quittance')
                ->where('statut', 'paye')
                ->latest('date_paiement')
                ->first();
        } elseif ($locataireId && in_array($user->role, ['admin', 'proprietaire'])) {
            $dernierPaiementPaye = Paiement::whereHas('location', fn($q) => $q->where('locataire_id', $locataireId))
                ->with('location.bien', 'location.locataire', 'quittance')
                ->where('statut', 'paye')
                ->latest('date_paiement')
                ->first();
        }

        // Tri : oldest si locataire OU si admin filtre par locataire (mois courant visible en premier)
        // Latest par défaut pour la vue de gestion générale
        $useOldest = $user->role === 'locataire' || ($locataireId && in_array($user->role, ['admin', 'proprietaire']));
        $paiements = $useOldest
            ? $query->oldest('date_echeance')->paginate(20)->withQueryString()
            : $query->latest('date_echeance')->paginate(20)->withQueryString();

        // Coût total des interventions du mois courant, indexé par bien_id
        $bienIds = match ($user->role) {
            'admin'        => null,
            'proprietaire' => $user->biens()->pluck('id'),
            default        => collect(),
        };
        $interventionsMois = Intervention::whereNotNull('cout')
            ->whereNotNull('date_intervention')
            ->whereMonth('date_intervention', now()->month)
            ->whereYear('date_intervention', now()->year)
            ->when($bienIds !== null, fn($q) => $q->whereIn('bien_id', $bienIds))
            ->groupBy('bien_id')
            ->selectRaw('bien_id, SUM(cout) as total')
            ->pluck('total', 'bien_id');

        return view('paiements.index', compact('paiements', 'biens', 'residences', 'locataires', 'dernierPaiementPaye', 'interventionsMois'));
    }

    public function marquerPaye(Request $request, Paiement $paiement)
    {
        $data = $request->validate([
            'methode_paiement' => 'required|in:virement,cheque,especes,prelevement,cb',
            'date_paiement'    => 'required|date',
            'reference'        => 'nullable|string|max:100',
        ]);

        $paiement->update([
            'statut'           => 'paye',
            'date_paiement'    => $data['date_paiement'],
            'methode_paiement' => $data['methode_paiement'],
            'reference'        => $data['reference'] ?? null,
        ]);

        // Génération automatique de quittance
        Quittance::create([
            'paiement_id'   => $paiement->id,
            'numero'        => Quittance::genererNumero(),
            'date_emission' => now(),
        ]);

        $paiement->load('quittance', 'location.bien.proprietaire', 'location.locataire');
        $this->envoyerQuittanceParMail($paiement);

        return back()->with('success', 'Paiement enregistré, quittance générée et envoyée par e-mail au locataire.');
    }

    public function relance(Paiement $paiement, RelanceIaService $service)
    {
        abort_if($paiement->statut === 'paye', 422);

        $paiement->load('location.bien.proprietaire', 'location.locataire');

        $ok = $service->envoyer($paiement, force: true);

        return back()->with(
            $ok ? 'success' : 'warning',
            $ok
                ? "Relance IA envoyée à {$paiement->location->locataire?->email} (relance n°{$paiement->nb_relances})."
                : 'Impossible d\'envoyer la relance — vérifiez l\'adresse e-mail du locataire.'
        );
    }

    // ─── Afficher une quittance (HTML) ───────────────────────────────────────
    public function telechargerQuittance(Quittance $quittance)
    {
        $user     = Auth::user();
        $paiement = $quittance->paiement->load(
            'location.bien.proprietaire',
            'location.locataire'
        );
        $location = $paiement->location;

        $peutVoir = $user->isAdmin()
            || $location->locataire_id === $user->id
            || optional($location->bien)->proprietaire_id === $user->id;

        abort_if(!$peutVoir, 403);

        return view('quittances.pdf', compact('quittance', 'paiement'));
    }

    // ─── Télécharger une quittance en PDF ────────────────────────────────────
    public function downloadQuittancePdf(Quittance $quittance)
    {
        $user     = Auth::user();
        $paiement = $quittance->paiement->load(
            'location.bien.proprietaire',
            'location.locataire'
        );
        $location = $paiement->location;

        $peutVoir = $user->isAdmin()
            || $location->locataire_id === $user->id
            || optional($location->bien)->proprietaire_id === $user->id;

        abort_if(!$peutVoir, 403);

        $pdf = Pdf::loadView('quittances.pdf-download', compact('quittance', 'paiement'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($quittance->numero . '.pdf');
    }

    // ─── Initier un paiement mobile (locataire) ──────────────────────────────
    public function initierMobile(Request $request, Paiement $paiement)
    {
        $user = Auth::user();
        abort_if($paiement->location->locataire_id !== $user->id, 403);
        abort_if($paiement->statut === 'paye', 422, 'Ce loyer est déjà réglé.');

        $request->validate([
            'canal'     => 'required|in:orange_money,mtn_money,wave,carte',
            'telephone' => 'nullable|string|max:20',
        ]);

        $provider = Parametre::get('paiement_provider', 'cinetpay');
        $apiKey   = Parametre::get('paiement_api_key');
        $canal    = $request->canal;

        // ── Mode simulation (aucune clé API configurée) ──────────────────────
        if (!$apiKey) {
            $paiement->update([
                'canal_paiement'     => $canal,
                'provider_reference' => 'SIMU_' . strtoupper(Str::random(8)),
                'statut'             => 'paye',
                'methode_paiement'   => 'mobile_money',
                'date_paiement'      => today(),
            ]);
            $this->genererQuittance($paiement);
            $this->notifierProprietaire($paiement);
            $paiement->load('quittance');
            return response()->json([
                'ok'           => true,
                'simulation'   => true,
                'quittance_id' => $paiement->quittance?->id,
            ]);
        }

        // ── Paiement réel ────────────────────────────────────────────────────
        $token = Str::random(48);
        $paiement->update(['payment_token' => $token, 'canal_paiement' => $canal]);

        try {
            $result = $provider === 'stripe'
                ? $this->initierStripeLoyer($paiement)
                : $this->initierCinetpayLoyer($paiement, $canal);

            $paiement->update([
                'payment_url'        => $result['url'],
                'provider_reference' => $result['reference'],
            ]);

            return response()->json(['ok' => true, 'url' => $result['url']]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Retour depuis la passerelle ─────────────────────────────────────────
    public function retourMobile(Request $request)
    {
        $paiement = Paiement::where('payment_token', $request->get('payment_token'))->first();
        if ($paiement) {
            $this->verifierPaiementLoyer($paiement);
        }
        return redirect()->route('paiements.index')
            ->with('success', 'Paiement traité. Votre quittance est disponible.');
    }

    // ─── Webhook passerelle de paiement ──────────────────────────────────────
    public function webhookLoyer(Request $request)
    {
        $ref = $request->get('cpm_trans_id') ?? $request->get('transaction_id');
        if ($ref) {
            $paiement = Paiement::where('provider_reference', $ref)->first();
            if ($paiement) {
                $this->verifierPaiementLoyer($paiement);
            }
        }
        return response('OK');
    }

    // ─── CinetPay pour loyer ─────────────────────────────────────────────────
    private function initierCinetpayLoyer(Paiement $p, string $canal): array
    {
        $apiKey = Parametre::get('paiement_api_key');
        $siteId = Parametre::get('paiement_site_id');

        if (!$apiKey || !$siteId) {
            throw new \Exception('Clés CinetPay manquantes. Configurez-les dans Administration > Config. APIs.');
        }

        $channelMap = [
            'orange_money' => 'ORANGE_MONEY',
            'mtn_money'    => 'MTN_MONEY',
            'wave'         => 'WAVE',
            'carte'        => 'CREDIT_CARD',
        ];

        $transId  = 'LOYER_' . $p->id . '_' . time();
        $locataire = $p->location->locataire;

        $payload = [
            'apikey'                => $apiKey,
            'site_id'               => $siteId,
            'transaction_id'        => $transId,
            'amount'                => (int) $p->montant,
            'currency'              => Parametre::get('paiement_devise', 'XOF'),
            'description'           => 'Loyer — ' . $p->location->bien->titre . ' (' . $p->date_echeance->format('m/Y') . ')',
            'return_url'            => route('paiements.retour-mobile', ['payment_token' => $p->payment_token]),
            'notify_url'            => route('paiements.webhook-loyer'),
            'channels'              => $channelMap[$canal] ?? 'ALL',
            'customer_name'         => explode(' ', $locataire->name)[0] ?? '',
            'customer_surname'      => explode(' ', $locataire->name)[1] ?? '',
            'customer_email'        => $locataire->email,
            'customer_phone_number' => $locataire->phone ?? '',
            'customer_address'      => $p->location->bien->adresse ?? '',
            'customer_city'         => $p->location->bien->ville ?? '',
            'customer_country'      => 'CI',
            'customer_state'        => 'CI',
            'customer_zip_code'     => '00000',
        ];

        $response = Http::timeout(20)->post('https://api-checkout.cinetpay.com/v2/payment', $payload);

        if (!$response->successful() || $response->json('code') !== '201') {
            throw new \Exception($response->json('message', 'Erreur CinetPay'));
        }

        $p->update(['provider_reference' => $transId]);

        return [
            'reference' => $transId,
            'url'       => $response->json('data.payment_url'),
            'raw'       => $response->json('data'),
        ];
    }

    // ─── Stripe pour loyer ────────────────────────────────────────────────────
    private function initierStripeLoyer(Paiement $p): array
    {
        $secretKey = Parametre::get('stripe_secret_key');
        if (!$secretKey) {
            throw new \Exception('Clé Stripe manquante.');
        }

        $response = Http::withBasicAuth($secretKey, '')->timeout(20)->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[]'                               => 'card',
                'line_items[0][price_data][currency]'                  => strtolower(Parametre::get('paiement_devise', 'eur')),
                'line_items[0][price_data][product_data][name]'        => 'Loyer — ' . $p->location->bien->titre,
                'line_items[0][price_data][product_data][description]' => $p->date_echeance->isoFormat('MMMM YYYY'),
                'line_items[0][price_data][unit_amount]'               => (int) ($p->montant * 100),
                'line_items[0][quantity]'                              => 1,
                'mode'                                                 => 'payment',
                'success_url'                                          => route('paiements.retour-mobile', ['payment_token' => $p->payment_token]),
                'cancel_url'                                           => route('paiements.index'),
                'customer_email'                                       => $p->location->locataire->email,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json('error.message', 'Erreur Stripe'));
        }

        $session = $response->json();
        return ['reference' => $session['id'], 'url' => $session['url'], 'raw' => $session];
    }

    // ─── Vérification paiement loyer ─────────────────────────────────────────
    private function verifierPaiementLoyer(Paiement $p): void
    {
        $provider = Parametre::get('paiement_provider', 'cinetpay');

        try {
            $paye = false;

            if ($provider === 'stripe') {
                $r = Http::withBasicAuth(Parametre::get('stripe_secret_key'), '')
                    ->get('https://api.stripe.com/v1/checkout/sessions/' . $p->provider_reference);
                $paye = $r->successful() && $r->json('payment_status') === 'paid';
            } else {
                $r = Http::timeout(15)->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                    'apikey'         => Parametre::get('paiement_api_key'),
                    'site_id'        => Parametre::get('paiement_site_id'),
                    'transaction_id' => $p->provider_reference,
                ]);
                $code = $r->json('data.status') ?? $r->json('code');
                $paye = in_array($code, ['ACCEPTED', '00']);
            }

            if ($paye) {
                $p->update([
                    'statut'           => 'paye',
                    'methode_paiement' => 'mobile_money',
                    'date_paiement'    => today(),
                ]);
                $this->genererQuittance($p);
                $this->notifierProprietaire($p);
            }
        } catch (\Exception) {
            // Silently fail
        }
    }

    // ─── Génération quittance + envoi email ──────────────────────────────────
    private function genererQuittance(Paiement $p): void
    {
        if (!$p->quittance) {
            Quittance::create([
                'paiement_id'   => $p->id,
                'numero'        => Quittance::genererNumero(),
                'date_emission' => now(),
            ]);
            $p->load('quittance', 'location.bien.proprietaire', 'location.locataire');
        }
        $this->envoyerQuittanceParMail($p);
    }

    // ─── Envoi de la quittance par e-mail au locataire ────────────────────────
    private function envoyerQuittanceParMail(Paiement $p): void
    {
        try {
            $email = optional($p->location->locataire)->email;
            if ($email && $p->quittance) {
                Mail::to($email)->queue(new QuittanceMail($p));
            }
        } catch (\Exception) {
            // Ne pas bloquer si l'envoi échoue
        }
    }

    // ─── Notifier le propriétaire du bien ────────────────────────────────────
    private function notifierProprietaire(Paiement $p): void
    {
        try {
            $proprietaire = optional($p->location->bien)->proprietaire;
            if ($proprietaire) {
                $proprietaire->notify(new PaiementRecuNotification($p));
            }
        } catch (\Exception) {
            // Ne pas bloquer le paiement si la notification échoue
        }
    }

    // ─── Espace locataire : mes règlements ───────────────────────────────────
    public function mesReglements()
    {
        $user = Auth::user();
        abort_if($user->role !== 'locataire', 403);

        $location = $user->locations()
            ->where('statut', 'actif')
            ->with('bien', 'bien.proprietaire')
            ->first();

        $paiements = $location
            ? Paiement::where('location_id', $location->id)
                ->with('quittance')
                ->oldest('date_echeance')
                ->get()
            : collect();

        $dernierPaye = $paiements->where('statut', 'paye')->sortByDesc('date_paiement')->first();
        $prochaine   = $paiements->where('statut', 'en_attente')->sortBy('date_echeance')->first();

        return view('locataire.mes-reglements', compact('paiements', 'location', 'dernierPaye', 'prochaine'));
    }
}
