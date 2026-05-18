<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'montant', 'categorie', 'date_depense', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant'      => 'decimal:2',
    ];

    const CATEGORIES = [
        'loyer_bureau' => ['label' => 'Loyer bureau',     'icon' => 'bi-building',          'color' => '#7C3AED'],
        'salaires'     => ['label' => 'Salaires',          'icon' => 'bi-people-fill',        'color' => '#2563EB'],
        'fournitures'  => ['label' => 'Fournitures',       'icon' => 'bi-box-seam',           'color' => '#D97706'],
        'publicite'    => ['label' => 'Publicité',         'icon' => 'bi-megaphone-fill',     'color' => '#EA580C'],
        'transport'    => ['label' => 'Transport',         'icon' => 'bi-car-front-fill',     'color' => '#16A34A'],
        'informatique' => ['label' => 'Informatique',      'icon' => 'bi-laptop',             'color' => '#0891B2'],
        'autres'       => ['label' => 'Autres',            'icon' => 'bi-three-dots',         'color' => '#6B7280'],
    ];

    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }
}
