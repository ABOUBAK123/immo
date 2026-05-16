<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'montant', 'date_echeance', 'date_paiement',
        'statut', 'type', 'methode_paiement', 'reference', 'note',
        'canal_paiement', 'payment_token', 'payment_url', 'provider_reference',
        'nb_relances', 'derniere_relance_at',
    ];

    const CANAUX_MOBILE = [
        'orange_money' => ['label' => 'Orange Money',  'color' => '#FF6B00', 'bg' => '#FFF3E8', 'emoji' => '🍊'],
        'mtn_money'    => ['label' => 'MTN MoMo',      'color' => '#FFC107', 'bg' => '#FFFDE7', 'emoji' => '💛'],
        'wave'         => ['label' => 'Wave',           'color' => '#009EE3', 'bg' => '#E3F4FD', 'emoji' => '🌊'],
        'carte'        => ['label' => 'Carte bancaire', 'color' => '#6366F1', 'bg' => '#EEF2FF', 'emoji' => '💳'],
    ];

    protected $casts = [
        'date_echeance'      => 'date',
        'date_paiement'      => 'date',
        'derniere_relance_at' => 'datetime',
        'montant'            => 'decimal:2',
    ];

    public function location()  { return $this->belongsTo(Location::class); }
    public function quittance() { return $this->hasOne(Quittance::class); }

    public function isEnRetard(): bool
    {
        return $this->statut === 'en_attente' && $this->date_echeance->isPast();
    }
}
