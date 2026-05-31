<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'user_id', 'formule_id', 'montant', 'devise',
        'date_debut', 'date_fin', 'statut',
        'methode_paiement', 'canal_paiement',
        'provider_reference', 'payment_token', 'payment_url',
        'invoice_number', 'essai',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'montant'    => 'decimal:2',
        'essai'      => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function formule() { return $this->belongsTo(FormuleAbonnement::class, 'formule_id'); }

    public function nomFormule(): string
    {
        return $this->formule?->nom ?? 'Standard';
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif' && $this->date_fin->isFuture();
    }

    public function joursRestants(): int
    {
        return max(0, (int) now()->diffInDays($this->date_fin, false));
    }

    public static function genererNumeroFacture(): string
    {
        $dernier = static::latest()->first();
        $num = $dernier ? (int) substr($dernier->invoice_number ?? '0', -6) + 1 : 1;
        return 'ABO-' . date('Y') . '-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    public function deviseSymbole(): string
    {
        return User::DEVISES[$this->devise ?? 'XOF']['symbole'] ?? $this->devise;
    }
}
