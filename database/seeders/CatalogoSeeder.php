<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Cliente,Autoridad,Etiqueta,Abogado};

  class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::insert([
            ['nombre' => 'Armonía Corporativo Vallartense S.A. de C.V.'],
            ['nombre' => 'Cliente Ejemplo'],
        ]);

        Autoridad::insert([
            ['nombre' => 'SAT Vallarta'],
            ['nombre' => 'SAT Guadalajara'],
        ]);

        Abogado::insert([
            ['nombre' => 'JUAN'],
            ['nombre' => 'MARIA'],
        ]);

        Etiqueta::insert([
            ['nombre' => 'ASUNTOS VALLARTA'],
            ['nombre' => 'URGENTE'],
        ]);
    }
}
