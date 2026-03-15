<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id', 'sender_id', 'contenu', 'type', 'fichier', 'duree',
        'supprime_pour_tous', 'supprimes_pour',
    ];

    protected $casts = [
        'supprime_pour_tous' => 'boolean',
        'supprimes_pour'     => 'array',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
