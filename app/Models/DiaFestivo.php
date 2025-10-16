<?php
namespace App\Models;
use Carbon\Carbon;


use Illuminate\Database\Eloquent\Model;

class DiaFestivo extends Model
{
     protected $table = 'dias_festivos';
    protected $fillable = ['autoridad_id','anio','fecha','descripcion'];
    protected $casts = ['fecha' => 'date'];
    function sumarDiasHabiles(Carbon $fecha, $dias) {
    $festivos = DiaFestivo::pluck('fecha')->toArray();

    while ($dias > 0) {
        $fecha->addDay();
        if ($fecha->isWeekend()) continue;
        if (in_array($fecha->toDateString(), $festivos)) continue;
        $dias--;
    }
    return $fecha;
}
}
