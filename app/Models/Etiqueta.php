<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
     protected $table = 'etiquetas'; 
     protected $fillable=['nombre']; 

    public function juicios()
    {
        return $this->belongsToMany(Etiqueta::class, 'etiqueta_juicio', 'juicio_id', 'etiqueta_id')->withTimestamps(); // solo si tu pivote tiene timestamps
    }
}