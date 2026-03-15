<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeInfraction extends Model
{
    use HasFactory;

    protected $table = 'types_infractions';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'categorie',
        'amende_min',
        'amende_max',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'amende_min' => 'decimal:2',
        'amende_max' => 'decimal:2',
    ];

    public function infractions()
    {
        return $this->hasMany(Infraction::class);
    }

    public function amendes()
    {
        return $this->hasMany(Amende::class);
    }
}
