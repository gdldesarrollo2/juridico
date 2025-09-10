<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = 'revisiones';

  protected $fillable = [
    'idempresa','autoridad_id','usuario_id',
    'rev_gabinete','rev_domiciliaria','rev_electronica','rev_secuencial',
    'revision','periodo_desde','periodo_hasta',
    'objeto','observaciones','aspectos','compulsas',
    'estatus',
  ];

  protected $casts = [
    'periodo_desde' => 'date',
    'periodo_hasta' => 'date',
    'rev_gabinete' => 'boolean',
    'rev_domiciliaria' => 'boolean',
    'rev_electronica' => 'boolean',
    'rev_secuencial' => 'boolean',
  ];
// NUEVA relación
   public function empresa()
{
    // FK = empresa_id en revisiones → PK = id en empresas
    return $this->belongsTo(Empresa::class, 'empresa_id', 'idempresa');
}
  public function autoridad(){ return $this->belongsTo(Autoridad::class); }
  public function usuario(){ return $this->belongsTo(User::class,'usuario_id'); }
  public function etiquetas(){ return $this->belongsToMany(Etiqueta::class, 'etiqueta_revision'); }
  public function etapas(){ return $this->hasMany(RevisionEtapa::class)->orderBy('fecha_inicio','desc'); }
}