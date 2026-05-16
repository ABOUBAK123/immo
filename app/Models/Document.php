<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'documentable_id', 'documentable_type', 'uploaded_by',
        'nom', 'type', 'chemin', 'taille', 'mime_type',
    ];

    public function documentable() { return $this->morphTo(); }
    public function uploader()     { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getTailleHumaine(): string
    {
        $taille = $this->taille ?? 0;
        if ($taille < 1024) return $taille . ' o';
        if ($taille < 1048576) return round($taille / 1024, 1) . ' Ko';
        return round($taille / 1048576, 1) . ' Mo';
    }
}
