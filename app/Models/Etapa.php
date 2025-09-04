<?php

// app/Models/Etapa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    // app/Models/Etapa.php
protected $fillable = [
    'juicio_id','etiqueta_id','etapa','usuario_id','rol','comentarios',
    'fecha_inicio',           // ✅
    'dias_vencimiento','fecha_vencimiento','estatus','archivo_path'
];

protected $casts = [
    'fecha_inicio'      => 'date:Y-m-d',   // ✅
    'fecha_vencimiento' => 'date:Y-m-d',
    'dias_vencimiento'  => 'integer',
];

    public function juicio(){ return $this->belongsTo(Juicio::class); }
    public function etiqueta(){ return $this->belongsTo(Etiqueta::class); }
    public function usuario(){ return $this->belongsTo(User::class, 'usuario_id'); }
}
