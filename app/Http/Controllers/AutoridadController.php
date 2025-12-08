<?php
namespace App\Http\Controllers;

use App\Models\Autoridad;
use Illuminate\Http\Request;

class AutoridadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'  => 'required|string|max:255',
            'estatus' => 'required|in:activo,inactivo',
        ]);

        $autoridad = Autoridad::create($validated);

        return back()->with('autoridadNueva', [
            'id' => $autoridad->id,
            'nombre' => $autoridad->nombre,
        ]);
    }
}
