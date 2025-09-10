<?php
// app/Models/RevisionEtapa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionEtapa extends Model
{
  protected $table = 'revision_etapas';

  protected $fillable = [
    'revision_id','abogado_id','usuario_id',
    'orden','nombre','fecha_inicio','dias_vencimiento','vence',
    'comentarios','estatus',
  ];

  protected $casts = [
    'fecha_inicio' => 'date',
    'vence' => 'date',
  ];

  public function revision(){ return $this->belongsTo(Revision::class); }
  public function abogado(){ return $this->belongsTo(Abogado::class); }
  public function usuario(){ return $this->belongsTo(User::class,'usuario_id'); }
}
