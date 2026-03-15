<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'genre',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'telephone',
        'email',
        'adresse',
        'grade',
        'fonction',
        'date_recrutement',
        'service_id',
        'statut',
        'photo',
        'observations',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_recrutement' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementAgent::class);
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
        return $this->belongsToMany(ServiceRetribue::class, 'service_retribue_agent')
            ->withPivot('role', 'remuneration')
            ->withTimestamps();
    }

    public function immigrationsClandestines()
    {
        return $this->hasMany(ImmigrationClandestine::class);
    }

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
