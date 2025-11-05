<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password; // 👈 AÑADE ESTA

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
     // ========== NUEVO: formulario de creación ==========
   public function create()
{
    $roles = Role::orderBy('name')->pluck('name');

    return Inertia::render('Seguridad/Usuarios/Create', [
        'roles' => $roles,
    ]);
}

    // ========== NUEVO: guardar usuario ==========
    public function store(Request $request)
    {
       $data = $request->validate([
         'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'role'  => ['required', 'string', 'exists:roles,name'],
        ]);

        // Generamos una contraseña aleatoria (no se la diremos al usuario)
        $randomPassword = Str::random(16);

        // Creamos al usuario
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($randomPassword),
        ]);

        // Asignamos rol
        $user->assignRole($data['role']);

        // Enviamos link de "restablecer contraseña" para que el propio usuario defina la suya.
        Password::sendResetLink(['email' => $user->email]);

        return redirect()
            ->route('users.roles.index')
            ->with('success', 'Usuario creado. Se envió un correo para que defina su contraseña.');
    }

}
