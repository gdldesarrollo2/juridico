<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AbogadoController extends Controller
{
    /**
     * Listado de abogados
     */
    public function index()
    {
       $abogados = Abogado::query()
        // si usas select, incluye usuario_id para que la relación funcione
        ->select(['id','nombre','estatus','usuario_id'])
        ->with([
            // cargamos el usuario con su email
            'usuario:id,name,email',
        ])
        ->withCount('juicios')
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Abogados/Index', [
        'abogados' => $abogados,
    ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        // Solo usuarios que no tienen abogado asignado
        $usuarios = User::whereDoesntHave('abogado')
            ->select('id','name','email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Abogados/Create', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Guardar nuevo abogado
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'estatus'    => ['required', Rule::in(['activo','inactivo'])],
            'usuario_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('abogados','usuario_id'),
            ],
        ]);

        Abogado::create($validated);

        return redirect()->route('abogados.index')
            ->with('success', 'Abogado creado correctamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit(Abogado $abogado)
    {
        // Usuarios sin abogado + el asignado actualmente
        $usuarios = User::whereDoesntHave('abogado')
            ->when($abogado->usuario_id, fn($q) => $q->orWhere('id', $abogado->usuario_id))
            ->select('id','name','email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Abogados/Edit', [
            'abogado' => [
                'id'         => $abogado->id,
                'nombre'     => $abogado->nombre,
                'estatus'    => $abogado->estatus,
                'usuario_id' => $abogado->usuario_id,
            ],
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Actualizar abogado
     */
    public function update(Request $request, Abogado $abogado)
    {
        $validated = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'estatus'    => ['required', Rule::in(['activo','inactivo'])],
            'usuario_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('abogados','usuario_id')->ignore($abogado->id),
            ],
        ]);

        $abogado->update($validated);

        return redirect()->route('abogados.index')
            ->with('success', 'Abogado actualizado correctamente.');
    }
}
