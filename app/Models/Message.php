<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'expediteur_id', 'destinataire_id', 'sujet', 'contenu', 'lu', 'lu_at',
    ];

    protected $casts = [
        'lu'    => 'boolean',
        'lu_at' => 'datetime',
    ];

    public function expediteur()    { return $this->belongsTo(User::class, 'expediteur_id'); }
    public function destinataire()  { return $this->belongsTo(User::class, 'destinataire_id'); }

    public function marquerLu(): void
    {
        if (!$this->lu) {
            $this->update(['lu' => true, 'lu_at' => now()]);
        }
    }
}
