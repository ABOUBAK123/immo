<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProfilConfig extends Model
{
    protected $fillable = ['role', 'module', 'label', 'icone', 'description', 'actif', 'verrouillee', 'ordre'];

    protected $casts = ['actif' => 'boolean', 'verrouillee' => 'boolean'];

    const ROLE_LABELS = [
        'proprietaire' => ['label' => 'Propriétaire',       'icone' => 'person-badge',  'color' => '#2563EB', 'bg' => '#EFF6FF'],
        'locataire'    => ['label' => 'Locataire',          'icone' => 'house-check',   'color' => '#16A34A', 'bg' => '#F0FDF4'],
        'agent'        => ['label' => 'Agence / Agent',     'icone' => 'buildings',     'color' => '#EA580C', 'bg' => '#FFF7ED'],
        'acheteur'     => ['label' => 'Client / Acheteur',  'icone' => 'person-search', 'color' => '#7C3AED', 'bg' => '#F5F3FF'],
    ];

    // Retourne [module => actif] pour un rôle, avec cache 5 min
    public static function moduleActifs(string $role): array
    {
        return Cache::remember("profil_config_{$role}", 300, function () use ($role) {
            return static::where('role', $role)
                ->orderBy('ordre')
                ->pluck('actif', 'module')
                ->map(fn($v) => (bool) $v)
                ->toArray();
        });
    }

    // Vérifie si un module est actif pour un rôle
    public static function isActif(string $role, string $module): bool
    {
        $configs = static::moduleActifs($role);
        return $configs[$module] ?? true;
    }

    // Vide le cache après une mise à jour
    public static function viderCache(string $role): void
    {
        Cache::forget("profil_config_{$role}");
    }
}
