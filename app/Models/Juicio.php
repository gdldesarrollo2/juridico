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
protected $appends = ['periodo_resumen'];

 public function getPeriodoResumenAttribute(): string
    {
        $p = $this->periodos;

        // Normalización: puede venir como string JSON, mapa {"2025":[1,2]}, o arreglo [{anio,meses}]
        try {
            if (is_string($p) && trim($p) !== '') {
                $p = json_decode($p, true);
            }
        } catch (\Throwable $e) {
            $p = [];
        }

        $arr = [];
        if (is_array($p)) {
            // Caso arreglo [{anio, meses}]
            if (isset($p[0]) && is_array($p[0]) && array_key_exists('anio',$p[0])) {
                foreach ($p as $item) {
                    $arr[] = [
                        'anio'  => (int) ($item['anio'] ?? 0),
                        'meses' => collect($item['meses'] ?? [])->map(fn($m)=>(int)$m)->sort()->values()->all(),
                    ];
                }
            } else {
                // Caso mapa {"2025":[1,2], ...}
                foreach ($p as $anio => $meses) {
                    $arr[] = [
                        'anio'  => (int) $anio,
                        'meses' => collect($meses ?? [])->map(fn($m)=>(int)$m)->sort()->values()->all(),
                    ];
                }
            }
        }

        if (empty($arr)) {
            return '—';
        }

        // Orden descendente por año
        usort($arr, fn($a,$b) => $b['anio'] <=> $a['anio']);

        $MES_ABBR = ['', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        $parts = array_map(function ($row) use ($MES_ABBR) {
            $anio  = $row['anio'];
            $meses = $row['meses'] ?? [];
            if (empty($meses)) return "{$anio}: —";
            $textoMeses = implode(', ', array_map(fn($m) => $MES_ABBR[$m] ?? $m, $meses));
            return "{$anio}: {$textoMeses}";
        }, $arr);

        return implode(' · ', $parts);
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
    public function abogadosHistorial()
{
    return $this->hasMany(\App\Models\JuicioAbogadoHistorial::class);
}
}
