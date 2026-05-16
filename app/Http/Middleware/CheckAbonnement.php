<?php

namespace App\Http\Middleware;

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

        // Avertissement si expiration dans moins de 5 jours
        $abonnement = $user->abonnementActif();
        if ($abonnement && $abonnement->joursRestants() <= 5) {
            session()->flash('abonnement_expire_bientot', $abonnement->joursRestants());
        }

        return $next($request);
    }
}
