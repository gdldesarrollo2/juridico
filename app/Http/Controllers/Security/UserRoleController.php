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
use Illuminate\Support\Facades\Password; 

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
    // 1) Validación
    $data = $request->validate([
        'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'role'  => ['required', 'string', 'exists:roles,name'],
    ]);

    // 2) Generar contraseña aleatoria (texto plano SOLO para el correo)
    $plainPassword = Str::random(12); // puedes cambiar longitud si quieres

    // 3) Crear usuario con la contraseña hasheada
    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($plainPassword),
    ]);

    // 4) Asignar rol
    $user->assignRole($data['role']);

    // 5) Enviar correo con sus datos de acceso
    //    Usamos una vista Blade: resources/views/emails/nuevo_usuario.blade.php
    Mail::send('emails.nuevo_usuario', [
        'user'     => $user,
        'password' => $plainPassword,
        'role'     => $data['role'],
        'loginUrl' => route('login'), // o url('/login') según tus rutas
    ], function ($message) use ($user) {
        $message->to($user->email, $user->name)
            ->subject('Tus accesos al sistema jurídico');
    });

    // 6) Redirigir de regreso al listado
    return redirect()
        ->route('users.roles.index')
        ->with('success', 'Usuario creado y credenciales enviadas por correo.');
}
}
