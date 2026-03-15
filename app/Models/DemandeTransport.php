<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeTransport extends Model
{
    use HasFactory;

    protected $table = 'demandes_transport';

    protected $fillable = [
        'accident_id', 'demandeur_id', 'transporteur_id', 'statut',
        'latitude_depart', 'longitude_depart', 'latitude_arrivee', 'longitude_arrivee',
        'notes', 'acceptee_at', 'terminee_at',
    ];

    protected $casts = [
        'acceptee_at' => 'datetime',
        'terminee_at' => 'datetime',
    ];

    public function accident()
    {
        return $this->belongsTo(Accident::class);
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function transporteur()
    {
        return $this->belongsTo(User::class, 'transporteur_id');
    }
}
