<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaFestivo extends Model
{
     protected $table = 'dias_festivos';
    protected $fillable = ['autoridad_id','anio','fecha','descripcion'];
    protected $casts = ['fecha' => 'date'];
}
