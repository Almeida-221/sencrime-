<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amende extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_amende',
        'infraction_id',
        'type_infraction_id',
        'date_amende',
        'nom_contrevenant',
        'prenom_contrevenant',
        'adresse_contrevenant',
        'telephone_contrevenant',
        'montant',
        'statut_paiement',
        'montant_paye',
        'date_paiement',
        'date_echeance',
        'mode_paiement',
        'reference_paiement',
        'localite',
        'region',
        'service_id',
        'agent_id',
        'user_id',
        'observations',
    ];

    protected $casts = [
        'date_amende' => 'date',
        'date_paiement' => 'date',
        'date_echeance' => 'date',
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
    ];

    public function infraction()
    {
        return $this->belongsTo(Infraction::class);
    }

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

    public function getMontantRestantAttribute()
    {
        return $this->montant - $this->montant_paye;
    }

    public function getContrevenantNomCompletAttribute()
    {
        return trim($this->prenom_contrevenant . ' ' . $this->nom_contrevenant);
    }
}
