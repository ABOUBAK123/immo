<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Annonce;
use App\Models\Intervention;
use App\Models\Location;
use App\Models\Paiement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = match ($user->role) {
            'admin'        => $this->dataAdmin(),
            'proprietaire' => $this->dataProprietaire($user),
            'locataire'    => $this->dataLocataire($user),
            'agent'        => $this->dataAgent($user),
            default        => $this->dataAcheteur(),
        };

        // Admin always XOF/default; locataire/agent have no currency display
        if (!isset($data['devise'])) {
            $data['devise'] = $user->devise ?? 'XOF';
        }

        return view('dashboard', $data);
    }

    private function dataAdmin(): array
    {
        $debutMois = Carbon::now()->startOfMonth();
        $finMois   = Carbon::now()->endOfMonth();

        $loyersMois  = Paiement::whereBetween('date_echeance', [$debutMois, $finMois])->sum('montant');
        $loyersPayes = Paiement::whereBetween('date_echeance', [$debutMois, $finMois])->where('statut', 'paye')->sum('montant');
        $retards     = Paiement::where('statut', 'en_attente')->where('date_echeance', '<', now())->get();
        $totalBiens  = Bien::count();
        $bienLoues   = Bien::where('statut', 'loue')->count();

        return [
            'nb_biens'          => $totalBiens,
            'nb_locations'      => Location::where('statut', 'actif')->count(),
            'nb_interventions'  => Intervention::where('statut', 'en_attente')->count(),
            'loyers_mois'       => $loyersMois,
            'loyers_payes'      => $loyersPayes,
            'loyers_retard'     => $retards->sum('montant'),
            'nb_retards'        => $retards->count(),
            'taux_occupation'   => $totalBiens > 0 ? round($bienLoues / $totalBiens * 100) : 0,
            'nb_urgences'       => Intervention::urgences()->count(),
            'alertes'           => $this->buildAlertes(),
            'derniers_paiements' => Paiement::with('location.bien', 'location.locataire')->latest()->limit(8)->get(),
            'urgences'          => Intervention::urgences()->with('bien')->limit(5)->get(),
            'biens_recents'     => Bien::latest()->limit(5)->get(),
        ];
    }

    private function dataProprietaire($user): array
    {
        $debutMois = Carbon::now()->startOfMonth();
        $finMois   = Carbon::now()->endOfMonth();
        $bienIds   = $user->biens()->pluck('id');
        $totalBiens = $bienIds->count();
        $bienLoues  = Bien::whereIn('id', $bienIds)->where('statut', 'loue')->count();

        $baseQuery = fn() => Paiement::whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $user->id));

        $loyersMois  = $baseQuery()->whereBetween('date_echeance', [$debutMois, $finMois])->sum('montant');
        $loyersPayes = $baseQuery()->whereBetween('date_echeance', [$debutMois, $finMois])->where('statut', 'paye')->sum('montant');
        $retards     = $baseQuery()->where('statut', 'en_attente')->where('date_echeance', '<', now())->get();

        return [
            'nb_biens'          => $totalBiens,
            'nb_locations'      => $bienLoues,
            'nb_interventions'  => Intervention::whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))->where('statut', 'en_attente')->count(),
            'loyers_mois'       => $loyersMois,
            'loyers_payes'      => $loyersPayes,
            'loyers_retard'     => $retards->sum('montant'),
            'nb_retards'        => $retards->count(),
            'taux_occupation'   => $totalBiens > 0 ? round($bienLoues / $totalBiens * 100) : 0,
            'nb_urgences'       => Intervention::urgences()->whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))->count(),
            'alertes'           => $this->buildAlertes($user->id),
            'derniers_paiements' => $baseQuery()->with('location.bien', 'location.locataire')->latest()->limit(8)->get(),
            'urgences'          => Intervention::urgences()->whereHas('bien', fn($q) => $q->where('proprietaire_id', $user->id))->with('bien')->limit(5)->get(),
            'biens_recents'     => $user->biens()->latest()->limit(5)->get(),
            'devise'            => $user->devise ?? 'XOF',
        ];
    }

    private function buildAlertes(?int $proprietaireId = null): \Illuminate\Support\Collection
    {
        $alertes = collect();

        // Paiements en retard
        $retardsQ = Paiement::where('statut', 'en_attente')->where('date_echeance', '<', now());
        if ($proprietaireId) {
            $retardsQ->whereHas('location.bien', fn($q) => $q->where('proprietaire_id', $proprietaireId));
        }
        $nbRetards = $retardsQ->count();
        if ($nbRetards > 0) {
            $alertes->push([
                'type'   => 'warning',
                'icon'   => 'exclamation-triangle',
                'titre'  => "$nbRetards paiement(s) en retard",
                'message' => 'Des locataires ont des loyers impayés. Pensez à les relancer.',
            ]);
        }

        // Baux expirant dans 30 jours
        $expirationsQ = Location::where('statut', 'actif')->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays(30)]);
        if ($proprietaireId) {
            $expirationsQ->whereHas('bien', fn($q) => $q->where('proprietaire_id', $proprietaireId));
        }
        $nbExpirations = $expirationsQ->count();
        if ($nbExpirations > 0) {
            $alertes->push([
                'type'   => 'info',
                'icon'   => 'calendar-event',
                'titre'  => "$nbExpirations bail(s) expirant dans 30 jours",
                'message' => 'Prenez contact avec vos locataires pour un renouvellement ou un congé.',
            ]);
        }

        return $alertes;
    }

    private function dataLocataire($user): array
    {
        $location = $user->locations()->where('statut', 'actif')->with('bien')->first();
        return [
            'location' => $location,
            'prochains_paiements' => $location
                ? Paiement::where('location_id', $location->id)->where('statut', 'en_attente')->orderBy('date_echeance')->limit(3)->get()
                : collect(),
            'dernier_paiement_paye' => $location
                ? Paiement::where('location_id', $location->id)->where('statut', 'paye')->with('quittance')->latest('date_paiement')->first()
                : null,
            'mes_interventions' => Intervention::where('locataire_id', $user->id)->latest()->limit(5)->get(),
        ];
    }

    private function dataAgent($user): array
    {
        return [
            'nb_annonces'     => $user->annonces()->where('statut', 'active')->count(),
            'nb_annonces_tot' => $user->annonces()->count(),
            'total_vues'      => $user->annonces()->sum('vues'),
            'nb_vendus'       => $user->annonces()->whereIn('statut', ['vendu', 'loue'])->count(),
            'annonces'        => $user->annonces()->with('bien')->latest()->limit(6)->get(),
            'devise'          => $user->devise ?? 'XOF',
        ];
    }

    private function dataAcheteur(): array
    {
        return [
            'annonces_recentes' => Annonce::actives()->with('bien')->latest()->limit(6)->get(),
        ];
    }
}
