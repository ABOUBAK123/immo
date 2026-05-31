<?php

namespace App\Services;

use App\Models\FormuleAbonnement;
use App\Models\User;

class PlanService
{
    // ─── Récupérer la formule active d'un utilisateur ─────────────────────────
    public static function formule(User $user): ?FormuleAbonnement
    {
        return $user->abonnementActif()?->formule;
    }

    // ─── Vérifier si une feature est autorisée ────────────────────────────────
    public static function peutAcceder(User $user, string $feature): bool
    {
        if ($user->role === 'admin') return true;

        $formule = static::formule($user);
        if (!$formule) return false;

        $champ = "has_{$feature}";
        return property_exists($formule, $champ) || isset($formule->$champ)
            ? (bool) $formule->$champ
            : true;
    }

    // ─── Vérifier si l'utilisateur a atteint une limite ──────────────────────
    public static function aAtteintLimite(User $user, string $ressource): bool
    {
        if ($user->role === 'admin') return false;

        $formule = static::formule($user);
        if (!$formule) return false;

        $champ = "max_{$ressource}";
        $limite = $formule->$champ ?? -1;
        if ($limite === -1) return false;

        return match ($ressource) {
            'biens'      => $user->biens()->count() >= $limite,
            'locataires' => $user->biens()->withCount('locations')->get()
                                ->sum(fn($b) => $b->locations()->distinct('locataire_id')->count()) >= $limite,
            'agents'     => false,
            'annonces'   => $user->biens()->withCount('annonces')->get()
                                ->sum(fn($b) => $b->annonces()->count()) >= $limite,
            default      => false,
        };
    }

    public static function limiteRestante(User $user, string $ressource): int
    {
        if ($user->role === 'admin') return PHP_INT_MAX;

        $formule = static::formule($user);
        if (!$formule) return 0;

        $champ = "max_{$ressource}";
        $limite = $formule->$champ ?? -1;
        if ($limite === -1) return PHP_INT_MAX;

        $utilise = match ($ressource) {
            'biens'      => $user->biens()->count(),
            'locataires' => $user->biens()->get()
                                ->sum(fn($b) => $b->locations()->distinct('locataire_id')->count()),
            'annonces'   => $user->biens()->get()
                                ->sum(fn($b) => $b->annonces()->count()),
            default      => 0,
        };

        return max(0, $limite - $utilise);
    }

    // ─── Message d'erreur standard pour upgrade requis ───────────────────────
    public static function messageUpgrade(string $ressource): string
    {
        return match ($ressource) {
            'biens'         => 'Vous avez atteint la limite de biens de votre formule. Passez à une formule supérieure.',
            'locataires'    => 'Vous avez atteint la limite de locataires de votre formule. Passez à une formule supérieure.',
            'annonces'      => 'Vous avez atteint la limite d\'annonces de votre formule. Passez à une formule supérieure.',
            'interventions' => 'La gestion des interventions n\'est pas disponible dans votre formule actuelle.',
            'depenses'      => 'Le suivi des dépenses n\'est pas disponible dans votre formule actuelle.',
            'ia'            => 'L\'agent IA n\'est pas disponible dans votre formule actuelle.',
            'agents'        => 'La gestion des agents n\'est pas disponible dans votre formule actuelle.',
            default         => 'Cette fonctionnalité n\'est pas disponible dans votre formule actuelle.',
        };
    }
}
