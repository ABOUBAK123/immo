<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Intervention;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'proprietaire'])) {
            return redirect()->route('dashboard');
        }

        $mois  = $request->get('mois', now()->format('Y-m'));
        [$annee, $moisNum] = explode('-', $mois);

        $depenses = Depense::where('created_by', $user->id)
            ->whereYear('date_depense', $annee)
            ->whereMonth('date_depense', $moisNum)
            ->latest('date_depense')
            ->get();

        return view('depenses.index', compact('depenses', 'mois'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'proprietaire'])) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'titre'        => 'required|string|max:255',
            'montant'      => 'required|numeric|min:0.01',
            'categorie'    => 'required|in:' . implode(',', array_keys(Depense::CATEGORIES)),
            'date_depense' => 'required|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $data['created_by'] = $user->id;
        Depense::create($data);

        return back()->with('success', 'Dépense enregistrée avec succès.');
    }

    public function destroy(Depense $depense)
    {
        $user = Auth::user();
        if ($depense->created_by !== $user->id && !$user->isAdmin()) {
            return back()->with('error', 'Action non autorisée.');
        }
        $depense->delete();
        return back()->with('success', 'Dépense supprimée.');
    }

    public function point(Request $request)
    {
        $user    = Auth::user();
        if (!in_array($user->role, ['admin', 'proprietaire'])) {
            return redirect()->route('dashboard');
        }

        $periode = $request->get('periode', 'mois');
        [$dateDebut, $dateFin, $periodeLabel] = $this->getPeriodeDates($periode);

        // Commissions agence = frais_agence% × loyer_mensuel sur paiements payés
        $paiementsPayes = Paiement::when(!$user->isAdmin(), function ($q) use ($user) {
                $q->whereHas('location.bien', fn($b) => $b->where('proprietaire_id', $user->id));
            })
            ->with('location')
            ->where('statut', 'paye')
            ->whereBetween('date_echeance', [$dateDebut, $dateFin])
            ->get();

        $totalCommissions = $paiementsPayes->sum(function ($p) {
            return round((float) $p->location->loyer_mensuel * (float) $p->location->frais_agence / 100, 2);
        });

        // Interventions sur la période
        $bienIds = $user->isAdmin()
            ? \App\Models\Bien::pluck('id')
            : $user->biens()->pluck('id');

        $totalInterventions = Intervention::whereNotNull('cout')
            ->whereNotNull('date_intervention')
            ->whereBetween('date_intervention', [$dateDebut, $dateFin])
            ->whereIn('bien_id', $bienIds)
            ->sum('cout');

        // Dépenses agence sur la période
        $depenses = Depense::where('created_by', $user->id)
            ->whereBetween('date_depense', [$dateDebut, $dateFin])
            ->latest('date_depense')
            ->get();

        $totalDepenses = $depenses->sum('montant');

        $benefice = $totalCommissions - $totalDepenses;

        $devSymbole = \App\Models\User::DEVISES[$user->devise ?? 'XOF']['symbole'] ?? ($user->devise ?? 'XOF');

        // Détail commissions par bien
        $commissionsParBien = $paiementsPayes->groupBy('location.bien_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'bien'       => $first->location->bien->titre ?? '—',
                    'count'      => $group->count(),
                    'pct'        => $first->location->frais_agence,
                    'commission' => $group->sum(fn($p) => round((float) $p->location->loyer_mensuel * (float) $p->location->frais_agence / 100, 2)),
                ];
            })
            ->filter(fn($b) => $b['pct'] > 0)
            ->values();

        $depensesParCategorie = $depenses->groupBy('categorie')
            ->map(fn($g) => $g->sum('montant'))
            ->sortDesc();

        return view('depenses.point', compact(
            'totalCommissions', 'totalInterventions', 'totalDepenses', 'benefice',
            'depenses', 'commissionsParBien', 'depensesParCategorie',
            'dateDebut', 'dateFin', 'periodeLabel', 'periode', 'devSymbole', 'user'
        ));
    }

    private function getPeriodeDates(string $periode): array
    {
        $now = now();
        switch ($periode) {
            case '2mois':
                $debut = $now->copy()->subMonth()->startOfMonth();
                return [$debut, $now->copy()->endOfMonth(),
                    ucfirst($debut->isoFormat('MMMM')) . ' – ' . ucfirst($now->isoFormat('MMMM YYYY'))];
            case 'trimestre':
                $debut = $now->copy()->subMonths(2)->startOfMonth();
                return [$debut, $now->copy()->endOfMonth(),
                    'Trimestre ' . ceil($now->month / 3) . ' ' . $now->year];
            case 'semestre':
                $debut = $now->copy()->subMonths(5)->startOfMonth();
                return [$debut, $now->copy()->endOfMonth(),
                    ($now->month <= 6 ? '1er' : '2ème') . ' semestre ' . $now->year];
            case 'annuel':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'Année ' . $now->year];
            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(),
                    ucfirst($now->isoFormat('MMMM YYYY'))];
        }
    }
}
