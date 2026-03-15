<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRetribue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services_retribues';

    protected $fillable = [
        'numero_mission',
        'titre',
        'description',
        'type_mission',
        'date_debut',
        'date_fin',
        'localite',
        'region',
        'client_nom',
        'client_telephone',
        'client_email',
        'client_adresse',
        'montant_total',
        'statut_paiement',
        'montant_paye',
        'date_paiement',
        'mode_paiement',
        'statut',
        'service_id',
        'user_id',
        'observations',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_paiement' => 'date',
        'montant_total' => 'decimal:2',
        'montant_paye' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'service_retribue_agent')
            ->withPivot('role', 'remuneration')
            ->withTimestamps();
    }

    public function getMontantRestantAttribute()
    {
        return $this->montant_total - $this->montant_paye;
    }
}
