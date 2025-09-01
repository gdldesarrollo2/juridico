<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autoridad extends Model
{
    protected $table = 'autoridades';   // 👈 aquí lo fuerzas
    protected $fillable=['nombre']; 
}
