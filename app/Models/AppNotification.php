<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'titre', 'message', 'type', 'icone', 'couleur', 'data', 'lien', 'lu', 'lu_at',
    ];

    protected $casts = [
        'data'   => 'array',
        'lu'     => 'boolean',
        'lu_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
