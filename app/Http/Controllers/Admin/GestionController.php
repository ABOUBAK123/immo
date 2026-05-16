<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Bien;
use App\Models\Intervention;
use App\Models\Location;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionController extends Controller
{
    private function checkAdmin()
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Accès réservé à l\'administrateur.');
    }

    // ─── Liste des propriétaires ─────────────────────────────────────────────
    public function proprietaires(Request $request)
    {
        $this->checkAdmin();

        $proprietaires = User::where('role', 'proprietaire')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%' . $request->q . '%';
                $q->where(fn($x) => $x->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('phone', 'like', $s));
            })
            ->withCount('biens')
            ->withCount(['biens as biens_loues_count' => fn($q) => $q->where('statut', 'loue')])
            ->with(['biens' => fn($q) => $q->select('id', 'proprietaire_id', 'statut')])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Statistiques globales
        $stats = [
            'total'         => User::where('role', 'proprietaire')->count(),
            'total_biens'   => Bien::count(),
            'biens_loues'   => Bien::where('statut', 'loue')->count(),
            'ce_mois'       => User::where('role', 'proprietaire')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.proprietaires.index', compact('proprietaires', 'stats'));
    }

    // ─── Fiche propriétaire ──────────────────────────────────────────────────
    public function showProprietaire(User $user)
    {
        $this->checkAdmin();
        abort_if($user->role !== 'proprietaire', 404);

        $user->load([
            'biens.locationActive.locataire',
            'biens.interventions' => fn($q) => $q->where('statut', '!=', 'termine'),
        ]);

        $bienIds = $user->biens->pluck('id');

        $stats = [
            'nb_biens'     => $bienIds->count(),
            'nb_loues'     => $user->biens->where('statut', 'loue')->count(),
            'loyers_mois'  => Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
                ->whereBetween('date_echeance', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('montant'),
            'loyers_payes' => Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
                ->whereBetween('date_echeance', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('statut', 'paye')->sum('montant'),
            'nb_retards'   => Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
                ->where('statut', 'en_attente')->where('date_echeance', '<', now())->count(),
            'nb_interventions' => Intervention::whereIn('bien_id', $bienIds)
                ->where('statut', '!=', 'termine')->count(),
        ];

        $derniers_paiements = Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id))
            ->with('location.bien', 'location.locataire', 'quittance')
            ->latest('date_echeance')->limit(10)->get();

        return view('admin.proprietaires.show', compact('user', 'stats', 'derniers_paiements'));
    }

    // ─── Liste des locataires ────────────────────────────────────────────────
    public function locataires(Request $request)
    {
        $this->checkAdmin();

        $locataires = User::where('role', 'locataire')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%' . $request->q . '%';
                $q->where(fn($x) => $x->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('phone', 'like', $s));
            })
            ->when($request->filled('statut'), function ($q) use ($request) {
                if ($request->statut === 'actif') {
                    $q->whereHas('locations', fn($l) => $l->where('statut', 'actif'));
                } elseif ($request->statut === 'sans_bail') {
                    $q->whereDoesntHave('locations', fn($l) => $l->where('statut', 'actif'));
                }
            })
            ->withCount('locations')
            ->with([
                'locations' => fn($q) => $q->where('statut', 'actif')
                    ->with('bien:id,titre,ville,adresse')
                    ->limit(1),
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'       => User::where('role', 'locataire')->count(),
            'actifs'      => User::where('role', 'locataire')
                ->whereHas('locations', fn($q) => $q->where('statut', 'actif'))->count(),
            'sans_bail'   => User::where('role', 'locataire')
                ->whereDoesntHave('locations', fn($q) => $q->where('statut', 'actif'))->count(),
            'ce_mois'     => User::where('role', 'locataire')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.locataires.index', compact('locataires', 'stats'));
    }

    // ─── Fiche locataire ─────────────────────────────────────────────────────
    public function showLocataire(User $user)
    {
        $this->checkAdmin();
        abort_if($user->role !== 'locataire', 404);

        $user->load([
            'locations' => fn($q) => $q->with('bien', 'paiements')->latest(),
        ]);

        $stats = [
            'nb_locations'  => $user->locations->count(),
            'location_actuelle' => $user->locations->where('statut', 'actif')->first(),
            'total_paye'    => Paiement::whereHas('location', fn($q) => $q->where('locataire_id', $user->id))
                ->where('statut', 'paye')->sum('montant'),
            'nb_retards'    => Paiement::whereHas('location', fn($q) => $q->where('locataire_id', $user->id))
                ->where('statut', 'en_attente')->where('date_echeance', '<', now())->count(),
        ];

        return view('admin.locataires.show', compact('user', 'stats'));
    }

    // ─── Agences / Agents immobiliers ───────────────────────────────────────────
    public function agences(Request $request)
    {
        $this->checkAdmin();

        $agents = User::where('role', 'agent')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%' . $request->q . '%';
                $q->where(fn($x) => $x->where('name', 'like', $s)->orWhere('email', 'like', $s));
            })
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->withCount(['annonces as nb_annonces'])
            ->withSum('annonces as total_vues', 'vues')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'   => User::where('role', 'agent')->count(),
            'actifs'  => User::where('role', 'agent')->where('statut', 'actif')->count(),
            'inactifs' => User::where('role', 'agent')->where('statut', 'inactif')->count(),
            'ce_mois' => User::where('role', 'agent')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.agences.index', compact('agents', 'stats'));
    }

    public function showAgent(User $user)
    {
        $this->checkAdmin();
        abort_if($user->role !== 'agent', 404);

        $annonces = Annonce::where('agent_id', $user->id)->with('bien')->latest()->get();

        $stats = [
            'nb_annonces' => $annonces->count(),
            'nb_actives'  => $annonces->where('statut', 'active')->count(),
            'total_vues'  => $annonces->sum('vues'),
            'nb_vendus'   => $annonces->whereIn('statut', ['vendu', 'loue'])->count(),
        ];

        return view('admin.agences.show', compact('user', 'annonces', 'stats'));
    }

    public function toggleStatutAgent(User $user)
    {
        $this->checkAdmin();
        abort_if($user->role !== 'agent', 404);

        $user->update([
            'statut' => $user->statut === 'actif' ? 'inactif' : 'actif',
        ]);

        $msg = $user->statut === 'actif' ? 'activé' : 'désactivé';
        return back()->with('success', "Compte de {$user->name} {$msg}.");
    }

    // ─── Devise d'un propriétaire (admin) ───────────────────────────────────────
    public function updateDevise(Request $request, User $user)
    {
        $this->checkAdmin();
        abort_if($user->role !== 'proprietaire', 404);

        $request->validate([
            'devise' => ['required', 'string', 'in:' . implode(',', array_keys(\App\Models\User::DEVISES))],
        ]);

        $user->update(['devise' => $request->devise]);

        return back()->with('success', 'Devise mise à jour en ' . $request->devise . ' pour ' . $user->name);
    }

    // ─── Suppression utilisateur ─────────────────────────────────────────────
    public function destroyUser(User $user)
    {
        $this->checkAdmin();
        abort_if($user->role === 'admin', 403, 'Impossible de supprimer un administrateur.');

        $role = $user->role;
        $user->delete();

        return redirect()
            ->route($role === 'proprietaire' ? 'admin.proprietaires' : 'admin.locataires')
            ->with('success', 'Compte supprimé avec succès.');
    }
}
