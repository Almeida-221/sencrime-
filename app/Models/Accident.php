<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_rapport',
        'date_accident',
        'heure_accident',
        'localite',
        'region',
        'lieu_exact',
        'latitude',
        'longitude',
        'type_accident',
        'description',
        'nombre_victimes',
        'nombre_blesses',
        'nombre_morts',
        'gravite',
        'causes',
        'statut',
        'service_id',
        'agent_id',
        'user_id',
        'observations',
        'note_vocale',
    ];

    protected $casts = [
        'date_accident' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

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

    public function photos()
    {
        return $this->hasMany(AccidentPhoto::class)->orderBy('ordre');
    }

    public function demandesTransport()
    {
        return $this->hasMany(DemandeTransport::class);
    }
}
