<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementAgent extends Model
{
    use HasFactory;

    protected $table = 'mouvements_agents';

    protected $fillable = [
        'agent_id',
        'service_origine_id',
        'service_destination_id',
        'type_mouvement',
        'date_mouvement',
        'motif',
        'observations',
        'user_id',
    ];

    protected $casts = [
        'date_mouvement' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function serviceOrigine()
    {
        return $this->belongsTo(Service::class, 'service_origine_id');
    }

    public function serviceDestination()
    {
        return $this->belongsTo(Service::class, 'service_destination_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
