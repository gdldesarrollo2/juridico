<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abogado extends Model
{
    protected $table = 'abogados';

    protected $fillable = [
        'nombre',
        'estatus',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
   public function juicios()
    {
        return $this->hasMany(Juicio::class, 'abogado_id');
    }
}