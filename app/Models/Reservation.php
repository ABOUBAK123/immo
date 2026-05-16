<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    protected $fillable = [
        'annonce_id', 'token', 'nom', 'prenom', 'email', 'telephone',
        'date_debut', 'date_fin', 'nb_voyageurs', 'nb_nuits',
        'prix_nuit', 'frais_service', 'montant_total',
        'statut', 'canal_paiement', 'reference_paiement', 'payment_url', 'metadata',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'metadata'     => 'array',
        'prix_nuit'    => 'decimal:2',
        'frais_service'=> 'decimal:2',
        'montant_total'=> 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($r) {
            $r->token = $r->token ?? Str::random(40);
        });
    }

    public function annonce(): BelongsTo
    {
        return $this->belongsTo(Annonce::class);
    }

    public static function datesOccupees(int $annonceId): array
    {
        return static::where('annonce_id', $annonceId)
            ->whereIn('statut', ['paiement_initie', 'payee', 'confirmee'])
            ->get(['date_debut', 'date_fin', 'nb_nuits'])
            ->flatMap(function ($r) {
                $dates = [];
                $current = $r->date_debut->copy();
                while ($current->lt($r->date_fin)) {
                    $dates[] = $current->format('Y-m-d');
                    $current->addDay();
                }
                return $dates;
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function statutLabel(): string
    {
        return match($this->statut) {
            'en_attente'       => 'En attente de paiement',
            'paiement_initie'  => 'Paiement en cours',
            'payee'            => 'Payée',
            'confirmee'        => 'Confirmée',
            'annulee'          => 'Annulée',
            default            => ucfirst($this->statut),
        };
    }

    public function canalLabel(): string
    {
        return match($this->canal_paiement) {
            'orange_money' => 'Orange Money',
            'mtn_money'    => 'MTN Mobile Money',
            'wave'         => 'Wave',
            'carte'        => 'Carte bancaire',
            'virement'     => 'Virement bancaire',
            default        => '—',
        };
    }
}
