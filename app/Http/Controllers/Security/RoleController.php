<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id','name']);

        return Inertia::render('Seguridad/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function edit(Role $role)
    {
        // Todos los permisos agrupados por "módulo" (antes del punto)
        $permissions = Permission::all()
            ->groupBy(function ($p) {
                return explode('.', $p->name)[0]; // ej. "juicios.view" -> "juicios"
            })
            ->map(function ($group) {
                return $group->map(fn($p) => ['id'=>$p->id, 'name'=>$p->name, 'label'=>$p->name]);
            });

        // Permisos ya asignados al rol
        $selected = $role->permissions()->pluck('name');

        return Inertia::render('Seguridad/Roles/Edit', [
            'role'        => ['id'=>$role->id, 'name'=>$role->name],
            'permissions' => $permissions, // { juicios: [{id,name},...], revisiones: [...] }
            'selected'    => $selected,    // ['juicios.view','juicios.create',...]
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Permisos actualizados.');
    }
}
