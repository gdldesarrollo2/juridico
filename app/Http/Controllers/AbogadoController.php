<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Abogado;
use App\Models\User;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AbogadoController extends Controller
{
    public function index()
    {
        $abogados = Abogado::with('usuario:id,name,email') // traemos usuario ligado
            ->withCount('juicios')                        // contamos juicios activos
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Abogados/Index', [
            'abogados' => $abogados,
        ]);
    }
    public function create()
    {
        // Solo usuarios sin abogado
        $usuarios = User::whereDoesntHave('abogado')
            ->orderBy('name')
            ->get(['id','name','email']);

        return Inertia::render('Abogados/Create', [
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'estatus'    => ['required', Rule::in(['activo','inactivo'])],
            'usuario_id' => [
                'nullable', 'exists:users,id',
                // Si tienes UNIQUE en abogados.usuario_id, esta regla evita duplicados:
                Rule::unique('abogados', 'usuario_id'),
            ],
        ]);

        Abogado::create($validated);

        return redirect()->route('abogados.index')
            ->with('success', 'Abogado creado correctamente.');
    }

    public function edit(Abogado $abogado)
    {
        // Usuarios SIN abogado + el actualmente asignado (para que no desaparezca del combo)
        $usuarios = User::whereDoesntHave('abogado')
            ->when($abogado->usuario_id, fn($q) => $q->orWhere('id', $abogado->usuario_id))
            ->orderBy('name')
            ->get(['id','name','email']);

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

    public function update(Request $request, Abogado $abogado)
    {
        $validated = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'estatus'    => ['required', Rule::in(['activo','inactivo'])],
            'usuario_id' => [
                'nullable', 'exists:users,id',
                // Permite repetir el mismo valor en su propio registro
                Rule::unique('abogados', 'usuario_id')->ignore($abogado->id),
            ],
        ]);

        $abogado->update($validated);

        return redirect()->route('abogados.index')
            ->with('success', 'Abogado actualizado correctamente.');
    }
}
