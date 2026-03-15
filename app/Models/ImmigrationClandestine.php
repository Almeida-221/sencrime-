<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmigrationClandestine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'immigrations_clandestines';

    protected $fillable = [
        'numero_cas',
        'date_interception',
        'localite',
        'region',
        'lieu_interception',
        'latitude',
        'longitude',
        'nombre_personnes',
        'nombre_hommes',
        'nombre_femmes',
        'nombre_mineurs',
        'nationalites',
        'pays_origine',
        'pays_destination',
        'moyen_transport',
        'type_operation',
        'statut',
        'description',
        'service_id',
        'agent_id',
        'user_id',
        'observations',
    ];

    protected $casts = [
        'date_interception' => 'date',
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
}
