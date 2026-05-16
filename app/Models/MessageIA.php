<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageIA extends Model
{
    protected $table = 'messages_ia';

    protected $fillable = ['conversation_id', 'role', 'contenu', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ConversationIA::class, 'conversation_id');
    }
}
