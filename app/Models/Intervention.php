<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'bien_id', 'locataire_id', 'prestataire_id', 'titre', 'description',
        'type', 'priorite', 'statut', 'date_demande', 'date_intervention',
        'cout', 'note_resolution', 'photos',
    ];

    protected $casts = [
        'photos'            => 'array',
        'date_demande'      => 'date',
        'date_intervention' => 'date',
        'cout'              => 'decimal:2',
    ];

    public function bien()        { return $this->belongsTo(Bien::class); }
    public function locataire()   { return $this->belongsTo(User::class, 'locataire_id'); }
    public function prestataire() { return $this->belongsTo(User::class, 'prestataire_id'); }
    public function documents()   { return $this->morphMany(Document::class, 'documentable'); }

    public function scopeUrgences($query) { return $query->where('priorite', 'urgente'); }
    public function scopeEnCours($query)  { return $query->where('statut', 'en_cours'); }
}
