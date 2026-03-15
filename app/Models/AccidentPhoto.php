<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccidentPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['accident_id', 'chemin', 'nom_original', 'ordre'];

    public function accident()
    {
        return $this->belongsTo(Accident::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->chemin);
    }
}
