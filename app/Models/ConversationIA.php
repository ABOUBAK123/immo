<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationIA extends Model
{
    protected $table = 'conversations_ia';

    protected $fillable = ['proprietaire_id', 'locataire_id', 'titre'];

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function locataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locataire_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MessageIA::class, 'conversation_id');
    }

    public function dernierMessage(): HasMany
    {
        return $this->hasMany(MessageIA::class, 'conversation_id')->latest()->limit(1);
    }
}
