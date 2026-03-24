<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'pin',
        'service_id',
        'region',
        'actif',
        'avatar',
        'modules_actifs',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'actif'             => 'boolean',
        'modules_actifs'    => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Région effective : la région directe ou celle du service rattaché
     */
    public function getRegionEffective(): ?string
    {
        return $this->region ?? $this->service?->region;
    }
}
