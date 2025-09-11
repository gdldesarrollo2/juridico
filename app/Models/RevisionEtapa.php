<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionEtapa extends Model
{
    protected $table = 'revision_etapas';
    protected $fillable = [
        'revision_id','nombre','catalogo_etapa_id','fecha_inicio','dias_vencimiento',
        'fecha_vencimiento','estatus','comentarios','abogado_id','usuario_captura_id'
    ];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
    ];
    public function usuarioCaptura(){ return $this->belongsTo(User::class,'usuario_captura_id'); }
}