<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
  // Mostrar formulario
    public function edit()
    {
        return Inertia::render('auth/ChangePassword');
    }

    // Guardar nueva contraseña
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // regla de Laravel
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Tu contraseña se actualizó correctamente.');
    }
}
