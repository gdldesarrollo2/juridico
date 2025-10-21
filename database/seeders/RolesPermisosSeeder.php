<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permisos = [
            'ver juicios',
            'crear juicios',
            'editar juicios',
            'eliminar juicios',
            'ver revisiones',
            'crear revisiones',
            'editar revisiones',
            'eliminar revisiones',
            'crear etapa de juicio',
            'ver abogados',
            'reasignar abogado',
            'nuevo cliente'
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Crear roles y asignar permisos
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $abogado = Role::firstOrCreate(['name' => 'abogado']);

        $admin->givePermissionTo(Permission::all());
        $abogado->givePermissionTo(['ver juicios','crear juicios','ver revisiones']);
    }
}
