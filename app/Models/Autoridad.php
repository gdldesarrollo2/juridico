<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autoridad extends Model
{
        protected $table = 'autoridades'; // 👈 esto resuelve el problema
    protected $fillable = ['nombre', 'estatus'];
}

?>