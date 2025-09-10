<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = ['idempresa',
        'rfc','razonsocial','empabrevia','tiposociedad','tipoesquema','activo',
        'replegal','domicilio','colonia','ciudad','estado','pais','codpostal',
        'tipo','recibos','factura','fintegracion','calle','exterior','interior',
        'delegacion','zg','curp','paterno','materno','nombre','oficio','usuario',
        'pass','correo','cadena_c','host_c','no_certificado',
        // ...y los demás que uses realmente
    ];
}
