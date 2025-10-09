<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Juicio;
use App\Support\JuicioAbogadoService;
class AbogadoController extends Controller
{
    /**
     * Mostrar listado de abogados.
     */
  public function index()
{
    $paginator = Abogado::query()
        ->withCount('juicios')       // ← genera juicios_count
        ->select('abogados.*')       // ← si usas select(s), esto evita que se pierda
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

    // En Laravel 10+ puedes usar through(); en 9 usa getCollection()->transform()
    $paginator->through(function ($a) {
        return [
            'id'            => $a->id,
            'nombre'        => $a->nombre,
            'estatus'       => $a->estatus,
            'juicios_count' => (int) ($a->juicios_count ?? 0), // ← lo mandamos seguro
            // agrega otras columnas que pintas en la tabla:
            // 'usuario'    => $a->usuario?->name ?? '—',
        ];
    });

    return Inertia::render('Abogados/Index', [
        'abogados' => $paginator,
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
    // public function update(Request $request, Abogado $abogado)
    // {
    //     $data = $request->validate([
    //         'nombre'  => ['required', 'string', 'max:255'],
    //         'estatus' => ['required', 'in:activo,inactivo'],
    //     ]);

    //     $abogado->update($data);

    //     return redirect()
    //         ->route('abogados.index')
    //         ->with('success', 'Abogado actualizado correctamente.');
    // }

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
    public function update(Request $request, Abogado $abogado)
    {
        $validated = $request->validate([
            'nombre'  => ['required','string','max:255'],
            'estatus' => ['required','in:activo,inactivo'],
        ]);

        // Si va a inactivo y tiene juicios asignados, redirigimos al flujo de reasignación
        if ($abogado->estatus !== 'inactivo' && $validated['estatus'] === 'inactivo') {
            $juicios = Juicio::with('cliente:id,nombre')
                ->where('abogado_id', $abogado->id)
                ->orderBy('fecha_inicio','desc')
                ->get(['id','nombre','cliente_id','abogado_id','fecha_inicio']);

            if ($juicios->count() > 0) {
                // Guardamos temporalmente nuevo estatus en sesión o params para aplicarlo luego
                return Inertia::render('Abogados/Reasignar', [
                    'abogado'        => $abogado->only(['id','nombre','estatus']),
                    'juicios'        => $juicios,
                    'abogadosActivos'=> Abogado::where('estatus','activo')->orderBy('nombre')->get(['id','nombre']),
                    'nextStatus'     => $validated['estatus'], // para aplicarlo al final
                ]);
            }
        }

        $abogado->update($validated);

        return redirect()->route('abogados.index')->with('success','Abogado actualizado.');
    }

    public function reasignarForm(Abogado $abogado)
    {
        $juicios = Juicio::with('cliente:id,nombre')
            ->where('abogado_id', $abogado->id)
            ->orderBy('fecha_inicio','desc')
            ->get(['id','nombre','cliente_id','abogado_id','fecha_inicio']);

        return Inertia::render('Abogados/Reasignar', [
            'abogado'        => $abogado->only(['id','nombre','estatus']),
            'juicios'        => $juicios,
            'abogadosActivos'=> Abogado::where('estatus','activo')->orderBy('nombre')->get(['id','nombre']),
            'nextStatus'     => 'inactivo',
        ]);
    }

    public function reasignarStore(Request $request, Abogado $abogado)
    {
        $data = $request->validate([
            'reasignaciones'         => ['required','array'],
            'reasignaciones.*.id'    => ['required','integer','exists:juicios,id'],
            'reasignaciones.*.nuevo_abogado_id' => ['nullable','integer','exists:abogados,id'],
            'nextStatus'             => ['required','in:activo,inactivo'],
        ]);

        foreach ($data['reasignaciones'] as $row) {
            $juicio = Juicio::findOrFail($row['id']);
            $nuevo  = $row['nuevo_abogado_id'] ?? null; // null = dejar sin abogado
            JuicioAbogadoService::setAbogado($juicio, $nuevo, $request->user()?->id, 'Reasignación por inactivación de abogado');
        }

        // finalmente, cambia el status del abogado
        $abogado->update(['estatus' => $data['nextStatus']]);

        return redirect()->route('abogados.index')->with('success','Juicios reasignados y abogado actualizado.');
    }
}
