<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormuleAbonnement extends Model
{
    protected $table = 'formules_abonnement';

    protected $fillable = [
        'nom', 'slug', 'description', 'couleur', 'icone', 'populaire',
        'prix_mensuel', 'prix_annuel', 'devise', 'duree_jours',
        'max_biens', 'max_locataires', 'max_agents', 'max_annonces',
        'has_interventions', 'has_annonces', 'has_depenses', 'has_ia',
        'has_agents', 'has_documents', 'has_export_pdf',
        'has_notifications_sms', 'has_api_access', 'support_prioritaire',
        'is_active', 'ordre',
    ];

    protected $casts = [
        'populaire'             => 'boolean',
        'has_interventions'     => 'boolean',
        'has_annonces'          => 'boolean',
        'has_depenses'          => 'boolean',
        'has_ia'                => 'boolean',
        'has_agents'            => 'boolean',
        'has_documents'         => 'boolean',
        'has_export_pdf'        => 'boolean',
        'has_notifications_sms' => 'boolean',
        'has_api_access'        => 'boolean',
        'support_prioritaire'   => 'boolean',
        'is_active'             => 'boolean',
    ];

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'formule_id');
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true)->orderBy('ordre');
    }

    public function estIllimite(string $champ): bool
    {
        return $this->$champ === -1;
    }

    public function limiteLabel(string $champ): string
    {
        $val = $this->$champ;
        return $val === -1 ? 'Illimité' : (string) $val;
    }

    public function prixFormate(string $periode = 'mensuel'): string
    {
        $montant = $periode === 'annuel' ? $this->prix_annuel : $this->prix_mensuel;
        $symbole = User::DEVISES[$this->devise]['symbole'] ?? $this->devise;
        return number_format($montant, 0, ',', ' ') . ' ' . $symbole;
    }

    public function peutAcceder(string $feature): bool
    {
        return (bool) $this->{"has_{$feature}"};
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }
}
