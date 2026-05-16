<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $fillable = ['groupe', 'cle', 'valeur', 'type', 'label'];

    // ─── Lire une clé ─────────────────────────────────────────────────────────
    public static function get(string $cle, mixed $defaut = null): mixed
    {
        return static::where('cle', $cle)->value('valeur') ?? $defaut;
    }

    // ─── Écrire ou créer une clé ──────────────────────────────────────────────
    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }

    // ─── Toutes les clés d'un groupe ──────────────────────────────────────────
    public static function groupe(string $groupe): \Illuminate\Support\Collection
    {
        return static::where('groupe', $groupe)->get()->keyBy('cle');
    }

    // ─── Vérifier si un groupe est configuré ──────────────────────────────────
    public static function groupeConfigured(string $groupe, array $clesRequises): bool
    {
        foreach ($clesRequises as $cle) {
            if (!static::where('cle', $cle)->whereNotNull('valeur')->where('valeur', '!=', '')->exists()) {
                return false;
            }
        }
        return true;
    }
}
