<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Annonce extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bien_id', 'agent_id', 'type', 'mode_location', 'prix', 'prix_nuit',
        'nb_max_voyageurs', 'equipements', 'prix_negociable',
        'titre', 'description', 'statut', 'date_disponibilite', 'vues', 'photos',
    ];

    protected $casts = [
        'photos'             => 'array',
        'equipements'        => 'array',
        'prix_negociable'    => 'boolean',
        'prix'               => 'decimal:2',
        'prix_nuit'          => 'decimal:2',
        'date_disponibilite' => 'date',
    ];

    public function bien()         { return $this->belongsTo(Bien::class); }
    public function agent()        { return $this->belongsTo(User::class, 'agent_id'); }
    public function reservations() { return $this->hasMany(\App\Models\Reservation::class); }

    public function estCourtTerme(): bool
    {
        return $this->mode_location === 'court_terme' && $this->prix_nuit > 0;
    }

    public function photoUrl(int $index = 0): ?string
    {
        $photos = $this->photos ?? $this->bien?->photos ?? [];
        return isset($photos[$index]) ? asset('storage/' . $photos[$index]) : null;
    }

    public function allPhotos(): array
    {
        $ap = $this->photos ?? [];
        $bp = $this->bien?->photos ?? [];
        $all = array_merge($ap, $bp);
        return array_map(fn($p) => asset('storage/' . $p), $all);
    }

    public function scopeActives($query)    { return $query->where('statut', 'active'); }
    public function scopeLocation($query)   { return $query->where('type', 'location'); }
    public function scopeVente($query)      { return $query->where('type', 'vente'); }
    public function scopeVille($query, $ville) { return $query->whereHas('bien', fn($q) => $q->where('ville', $ville)); }

    public function incrementerVues(): void
    {
        $this->increment('vues');
    }
}
