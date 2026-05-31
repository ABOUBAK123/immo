<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;

class CheckAbonnement
{
    // Routes accessibles même sans abonnement actif
    private const ROUTES_LIBRES = [
        'abonnements.*',
        'logout',
        'dashboard',
        'profil.*',
    ];

    // Mapping route → feature requise dans la formule
    private const ROUTE_FEATURES = [
        'interventions.*' => 'interventions',
        'depenses.*'      => 'depenses',
        'agent-ia.*'      => 'ia',
        'agent.*'         => 'agents',
        'annonces.*'      => 'annonces',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'proprietaire') {
            return $next($request);
        }

        // Laisser passer les routes libres
        foreach (self::ROUTES_LIBRES as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        if (!$user->aAbonnementActif()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Abonnement requis.'], 402);
            }
            return redirect()->route('abonnements.index')
                ->with('abonnement_requis', true);
        }

        // Avertissement expiration dans moins de 5 jours
        $abonnement = $user->abonnementActif();
        if ($abonnement && $abonnement->joursRestants() <= 5) {
            session()->flash('abonnement_expire_bientot', $abonnement->joursRestants());
        }

        // Vérification feature gate selon la formule
        foreach (self::ROUTE_FEATURES as $routePattern => $feature) {
            if ($request->routeIs($routePattern)) {
                if (!PlanService::peutAcceder($user, $feature)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => PlanService::messageUpgrade($feature),
                            'upgrade_required' => true,
                        ], 403);
                    }
                    return redirect()->route('abonnements.formules')
                        ->with('upgrade_requis', PlanService::messageUpgrade($feature));
                }
            }
        }

        return $next($request);
    }
}
