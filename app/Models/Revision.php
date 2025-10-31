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
     'rev_gabinete'     => 'bool',
    'rev_domiciliaria' => 'bool',
    'rev_electronica'  => 'bool',
    'rev_secuencial'   => 'bool',
    'periodos'         => 'array',
  ];
// (opcional) si quieres que siempre viaje al front:
protected $appends = ['periodo_etiqueta','ultima_etapa'];
 public function getTipoRevisionAttribute(): ?string
    {
        return match (true) {
            $this->rev_gabinete     => 'gabinete',
            $this->rev_domiciliaria => 'domiciliaria',
            $this->rev_electronica  => 'electronica',
            $this->rev_secuencial   => 'secuencial',
            default => null,
        };
    }

    public function getTipoRevisionLegibleAttribute(): string
    {
        $map = [
            'gabinete'    => 'Gabinete',
            'domiciliaria'=> 'Domiciliaria',
            'electronica' => 'Electrónica',
            'secuencial'  => 'Secuencial',
        ];
        return $map[$this->tipo_revision] ?? '—';
    }
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
public function ultimaEtapa()
{
    return $this->hasOne(RevisionEtapa::class, 'revision_id')
        ->orderByDesc('fecha_inicio')
        ->orderByDesc('id');
}

    /** Scope de filtros */
public function scopeFilter($q, array $f)
{
    $map = [
        'gabinete'     => 'rev_gabinete',
        'domiciliaria' => 'rev_domiciliaria',
        'electronica'  => 'rev_electronica',
        'secuencial'   => 'rev_secuencial',
    ];

    // nota: ahora leemos $f['t'] en vez de 'tipo'
    $q->when(!empty($f['t']), function ($q) use ($f, $map) {
        $tipo = strtolower(trim((string)$f['t']));
        if (isset($map[$tipo])) {
            $q->where($map[$tipo], 1);
        }
    });

    $q->when(!empty($f['sociedad_id']), fn($q) => $q->where('idempresa', $f['sociedad_id']));
    $q->when(!empty($f['autoridad_id']), fn($q) => $q->where('autoridad_id', $f['autoridad_id']));
    $q->when(!empty($f['usuario_id']),   fn($q) => $q->where('usuario_id', $f['usuario_id']));
    $q->when(!empty($f['estatus']),      fn($q) => $q->where('estatus', $f['estatus']));
    $q->when(!empty($f['q']), function ($q) use ($f) {
        $like = '%'.trim((string)$f['q']).'%';
        $q->where(function ($qq) use ($like) {
            $qq->where('observaciones','like',$like)
               ->orWhere('aspectos','like',$like)
               ->orWhere('compulsas','like',$like);
        });
    });
}


}