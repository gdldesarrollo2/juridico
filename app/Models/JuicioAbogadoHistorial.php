<?php
// app/Models/JuicioAbogadoHistorial.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JuicioAbogadoHistorial extends Model
{
    protected $table = 'juicio_abogado_historial';
    protected $fillable = [
        'juicio_id','abogado_id','asignado_desde','asignado_hasta','changed_by','motivo'
    ];

    public function juicio(){ return $this->belongsTo(Juicio::class); }
    public function abogado(){ return $this->belongsTo(Abogado::class); }
    public function usuario() { return $this->belongsTo(\App\Models\User::class, 'usuario_id'); }
}
