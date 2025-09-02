<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juicio extends Model
{
    protected $fillable = [
        'nombre','tipo','cliente_id','autoridad_id','fecha_inicio','monto',
        'observaciones_monto','resolucion_impugnada','garantia',
        'numero_juicio','numero_expediente','estatus','abogado_id'
    ];

    protected $casts = [
        'fecha_inicio' => 'date:d/m/Y',
        'monto' => 'decimal:2',
    ];

    public function cliente(){ return $this->belongsTo(Cliente::class); }
    public function autoridad(){ return $this->belongsTo(Autoridad::class); }
    public function etiquetas(){ return $this->belongsToMany(Etiqueta::class, 'etiqueta_juicio'); }
    public function abogado(){ return $this->belongsTo(Abogado::class); }
}
