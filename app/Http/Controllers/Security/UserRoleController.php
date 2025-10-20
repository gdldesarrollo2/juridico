<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id','name','email']);

        return Inertia::render('Seguridad/Usuarios/Index', [
            'users' => $users->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'roles' => $u->roles->pluck('name'),
            ]),
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get(['id','name']);
        $selected = $user->roles()->pluck('name');

        return Inertia::render('Seguridad/Usuarios/EditRole', [
            'user'     => ['id'=>$user->id, 'name'=>$user->name, 'email'=>$user->email],
            'roles'    => $roles,
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'   => ['array'],
            'roles.*' => ['string'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.roles.index')->with('success', 'Rol(es) asignado(s) correctamente.');
    }
}
