<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
   // app/Models/Juicio.php

protected $fillable = [
    'nombre','tipo','cliente_id','autoridad_id','fecha_inicio','monto','observaciones_monto',
    'resolucion_impugnada','garantia','numero_juicio','numero_expediente',
    'estatus','abogado_id', 'no_juicio', // si ya lo tienes
    'periodos', // 👈 nuevo
];

protected $casts = [
    'periodos' => 'array',
];

// Opcional: que viaje siempre al front
protected $appends = ['periodo_etiqueta'];

public function getPeriodoEtiquetaAttribute(): string
{
    if (!is_array($this->periodos) || empty($this->periodos)) return '— —';

    $mesCorto = [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    $map = $this->periodos;
    ksort($map, SORT_NUMERIC);

    $trozos = [];
    foreach ($map as $anio => $meses) {
        $arr = array_map('intval', (array)$meses);
        sort($arr, SORT_NUMERIC);
        $labels = array_map(fn($m) => $mesCorto[$m] ?? (string)$m, $arr);
        $trozos[] = $anio . ': ' . implode(', ', $labels);
    }
    return implode(' · ', $trozos);
}


    public function cliente(){ return $this->belongsTo(Cliente::class); }
    public function autoridad(){ return $this->belongsTo(Autoridad::class); }
    public function etiquetas(){ return $this->belongsToMany(Etiqueta::class, 'etiqueta_juicio'); }
    public function abogado(){ return $this->belongsTo(Abogado::class); }
    public function etapas()
    {
        // Asegúrate de importar App\Models\Etapa
        return $this->hasMany(\App\Models\Etapa::class)->latest('fecha_vencimiento');
        // Si prefieres otro orden:
        // return $this->hasMany(\App\Models\Etapa::class)->latest('id');
    }
}
