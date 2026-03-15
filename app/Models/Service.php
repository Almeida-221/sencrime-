<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'localite',
        'region',
        'telephone',
        'email',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function infractions()
    {
        return $this->hasMany(Infraction::class);
    }

    public function accidents()
    {
        return $this->hasMany(Accident::class);
    }

    public function amendes()
    {
        return $this->hasMany(Amende::class);
    }

    public function servicesRetribues()
    {
        return $this->hasMany(ServiceRetribue::class);
    }

    public function immigrationsClandestines()
    {
        return $this->hasMany(ImmigrationClandestine::class);
    }

    public function getEffectifAttribute()
    {
        return $this->agents()->where('statut', 'actif')->count();
    }
}
