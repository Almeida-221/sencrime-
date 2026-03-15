<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['nom', 'type', 'created_by'];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function dernierMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latest()->limit(1);
    }

    /** Nombre de messages non lus pour un user donné */
    public function nonLusPour(int $userId): int
    {
        $pivot = $this->participants()->where('user_id', $userId)->first()?->pivot;
        $since = $pivot?->last_read_at;

        $query = $this->messages()->where('sender_id', '!=', $userId);
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        return $query->count();
    }

    /** Nom affiché pour un user donné (pour les conversations directes) */
    public function nomPour(int $userId): string
    {
        if ($this->type === 'groupe') {
            return $this->nom ?? 'Groupe sans nom';
        }
        $other = $this->participants->firstWhere('id', '!=', $userId);
        return $other?->name ?? 'Inconnu';
    }
}
