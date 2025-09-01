<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
     protected $fillable=['nombre']; 

    public function juicios()
    {
        return $this->belongsToMany(Juicio::class, 'etiqueta_juicio');
    }
}