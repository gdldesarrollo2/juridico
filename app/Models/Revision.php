<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = 'revisiones';

  protected $fillable = [
    'idempresa','autoridad_id','usuario_id',
    'rev_gabinete','rev_domiciliaria','rev_electronica','rev_secuencial',
    'revision','periodos',                 // 👈 nuevo
    'objeto','observaciones','aspectos','compulsas',
    'estatus', 'no_juicio',   // <- nuevo campo
  ];

  protected $casts = [
    'periodos'         => 'array',   // <- JSON como array/objeto
    'rev_gabinete' => 'boolean',
    'rev_domiciliaria' => 'boolean',
    'rev_electronica' => 'boolean',
    'rev_secuencial' => 'boolean',
  ];
// (opcional) si quieres que siempre viaje al front:
protected $appends = ['periodo_etiqueta'];

public function getPeriodoEtiquetaAttribute(): string
{
    if (!is_array($this->periodos) || empty($this->periodos)) {
        return '— —';
    }

    $mesCorto = [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    // Ordena por año asc y meses asc (por si llega desordenado)
    $periodos = $this->periodos;
    ksort($periodos, SORT_NUMERIC);

    $trozos = [];
    foreach ($periodos as $anio => $meses) {
        $meses = array_map('intval', (array) $meses);
        sort($meses, SORT_NUMERIC);

        $labels = array_map(fn($m) => $mesCorto[$m] ?? (string)$m, $meses);
        $trozos[] = $anio . ': ' . implode(', ', $labels);
    }

    return implode(' · ', $trozos); // ej: "2022: Ene, Jun, Jul, Ago · 2025: Ene, Feb, Mar, Jun, Ago"
}
// NUEVA relación
public function empresa()
{
    return $this->belongsTo(\App\Models\Empresa::class, 'idempresa', 'idempresa');
}
  public function autoridad(){ return $this->belongsTo(related: Autoridad::class); }
  public function usuario(){ return $this->belongsTo(User::class,'usuario_id'); }
  public function etiquetas(){ return $this->belongsToMany(Etiqueta::class, 'etiqueta_revision'); }
  public function etapas(){ return $this->hasMany(RevisionEtapa::class)->orderBy('fecha_inicio','desc'); }
}