<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'estatus',
        'usuario_id',
    ];

    protected $casts = [
        'estatus' => 'string',
    ];

    // Relación con usuario capturador
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scope para filtrar activos
    public function scopeActivos($query)
    {
        return $query->where('estatus', 'activo');
    }
}