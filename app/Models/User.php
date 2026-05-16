<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'devise',
        'statut',
        'password',
    ];

    const DEVISES = [
        'XOF' => ['label' => 'Franc CFA (BCEAO)', 'symbole' => 'FCFA', 'flag' => '🌍'],
        'XAF' => ['label' => 'Franc CFA (BEAC)',  'symbole' => 'FCFA', 'flag' => '🌍'],
        'EUR' => ['label' => 'Euro',               'symbole' => '€',    'flag' => '🇪🇺'],
        'USD' => ['label' => 'Dollar US',          'symbole' => '$',    'flag' => '🇺🇸'],
        'MAD' => ['label' => 'Dirham Marocain',    'symbole' => 'MAD',  'flag' => '🇲🇦'],
        'DZD' => ['label' => 'Dinar Algérien',     'symbole' => 'DA',   'flag' => '🇩🇿'],
        'TND' => ['label' => 'Dinar Tunisien',     'symbole' => 'DT',   'flag' => '🇹🇳'],
    ];

    public function deviseSymbole(): string
    {
        return self::DEVISES[$this->devise ?? 'XOF']['symbole'] ?? $this->devise;
    }

    public function deviseLabel(): string
    {
        return self::DEVISES[$this->devise ?? 'XOF']['label'] ?? $this->devise;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isProprietaire(): bool { return $this->role === 'proprietaire'; }
    public function isLocataire(): bool    { return $this->role === 'locataire'; }
    public function isAgent(): bool        { return $this->role === 'agent'; }
    public function isActif(): bool        { return ($this->statut ?? 'actif') === 'actif'; }

    public function biens()         { return $this->hasMany(Bien::class, 'proprietaire_id'); }
    public function biensAgent()    { return $this->hasMany(Bien::class, 'agent_id'); }
    public function locations()     { return $this->hasMany(Location::class, 'locataire_id'); }
    public function annonces()      { return $this->hasMany(Annonce::class, 'agent_id'); }
    public function interventions() { return $this->hasMany(Intervention::class, 'locataire_id'); }
    public function abonnements()   { return $this->hasMany(Abonnement::class); }
    public function messageEnvoyes()    { return $this->hasMany(Message::class, 'expediteur_id'); }
    public function messageRecus()      { return $this->hasMany(Message::class, 'destinataire_id'); }

    public function abonnementActif(): ?\App\Models\Abonnement
    {
        return $this->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now()->toDateString())
            ->latest('date_fin')
            ->first();
    }

    public function aAbonnementActif(): bool
    {
        return $this->abonnementActif() !== null;
    }
}
