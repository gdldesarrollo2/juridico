<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbogadoController extends Controller
{
    /**
     * Mostrar listado de abogados.
     */
    public function index()
    {
        $abogados = Abogado::with('usuario')
            ->orderBy('nombre')
            ->paginate(10);

        return Inertia::render('Abogados/Index', [
            'abogados' => $abogados,
        ]);
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return Inertia::render('Abogados/Create');
    }

    /**
     * Guardar un nuevo abogado.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'  => ['required', 'string', 'max:255'],
            'estatus' => ['required', 'in:activo,inactivo'],
        ]);

        $data['usuario_id'] = auth()->id();

        Abogado::create($data);

        return redirect()
            ->route('abogados.index')
            ->with('success', 'Abogado creado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Abogado $abogado)
    {
        return Inertia::render('Abogados/Edit', [
            'abogado' => $abogado,
        ]);
    }

    /**
     * Actualizar abogado.
     */
    public function update(Request $request, Abogado $abogado)
    {
        $data = $request->validate([
            'nombre'  => ['required', 'string', 'max:255'],
            'estatus' => ['required', 'in:activo,inactivo'],
        ]);

        $abogado->update($data);

        return redirect()
            ->route('abogados.index')
            ->with('success', 'Abogado actualizado correctamente.');
    }

    /**
     * Eliminar abogado.
     */
    public function destroy(Abogado $abogado)
    {
        $abogado->delete();

        return redirect()
            ->route('abogados.index')
            ->with('success', 'Abogado eliminado correctamente.');
    }
}
