<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bien_id', 'locataire_id', 'date_debut', 'date_fin', 'loyer_mensuel',
        'charges', 'frais_agence', 'depot_garantie', 'type_bail', 'statut',
        'revision_loyer_date', 'index_irl', 'conditions_particulieres',
    ];

    protected $casts = [
        'date_debut'          => 'date',
        'date_fin'            => 'date',
        'revision_loyer_date' => 'date',
        'loyer_mensuel'       => 'decimal:2',
        'charges'             => 'decimal:2',
        'frais_agence'        => 'decimal:2',
        'depot_garantie'      => 'decimal:2',
    ];

    public function bien()      { return $this->belongsTo(Bien::class); }
    public function locataire() { return $this->belongsTo(User::class, 'locataire_id'); }
    public function paiements() { return $this->hasMany(Paiement::class); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }

    // Ce que paie le locataire (loyer saisi + charges ; les frais agence sont déjà inclus dans le loyer)
    public function getMontantTotalAttribute(): float
    {
        return (float) $this->loyer_mensuel + (float) $this->charges;
    }

    // Montant des frais d'agence déduit du loyer
    public function getMontantFraisAgenceAttribute(): float
    {
        return round((float) $this->loyer_mensuel * (float) $this->frais_agence / 100, 2);
    }

    // Ce que reçoit le propriétaire (loyer - frais agence)
    public function getMontantNetProprietaireAttribute(): float
    {
        return (float) $this->loyer_mensuel - $this->getMontantFraisAgenceAttribute();
    }
}
