<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Infraction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_dossier',
        'type_infraction_id',
        'date_infraction',
        'localite',
        'region',
        'description',
        'nom_contrevenant',
        'prenom_contrevenant',
        'date_naissance_contrevenant',
        'nationalite_contrevenant',
        'adresse_contrevenant',
        'statut',
        'service_id',
        'agent_id',
        'user_id',
        'observations',
        'note_vocale',
    ];

    protected $casts = [
        'date_infraction' => 'date',
        'date_naissance_contrevenant' => 'date',
    ];

    public function typeInfraction()
    {
        return $this->belongsTo(TypeInfraction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amendes()
    {
        return $this->hasMany(Amende::class);
    }

    public function getContrevenantNomCompletAttribute()
    {
        return trim($this->prenom_contrevenant . ' ' . $this->nom_contrevenant);
    }
}
