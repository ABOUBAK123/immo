<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bien extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proprietaire_id', 'agent_id', 'titre', 'type', 'nom_residence', 'surface', 'nb_pieces', 'nb_chambres',
        'nb_sdb', 'etage', 'adresse', 'ville', 'code_postal', 'pays',
        'latitude', 'longitude', 'description', 'statut', 'meuble',
        'prix_achat', 'valeur_estimee', 'annee_construction', 'dpe', 'photos',
    ];

    protected $casts = [
        'photos'  => 'array',
        'meuble'  => 'boolean',
        'surface' => 'decimal:2',
    ];

    public function proprietaire()  { return $this->belongsTo(User::class, 'proprietaire_id'); }
    public function agentCreateur() { return $this->belongsTo(User::class, 'agent_id'); }
    public function locations()     { return $this->hasMany(Location::class); }
    public function annonces()      { return $this->hasMany(Annonce::class); }
    public function interventions() { return $this->hasMany(Intervention::class); }
    public function documents()     { return $this->morphMany(Document::class, 'documentable'); }

    public function locationActive()
    {
        return $this->hasOne(Location::class)->where('statut', 'actif');
    }
}
