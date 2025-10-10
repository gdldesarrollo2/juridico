<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Juicio;
use App\Support\JuicioAbogadoService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AbogadoController extends Controller
{
    /**
     * Mostrar listado de abogados.
     */
public function index()
{
    // 1) Saca los conteos de juicios por abogado con UNA consulta
    $jt = (new Juicio)->getTable();

    $countsQuery = DB::table($jt)
        ->select('abogado_id', DB::raw('COUNT(*) as c'))
        ->groupBy('abogado_id');

    // Si tu tabla tiene soft-deletes y quieres excluir borrados:
    if (Schema::hasColumn($jt, 'deleted_at')) {
        $countsQuery->whereNull('deleted_at');
    }

    // ->pluck(valor, clave) => [abogado_id => c]
    $counts = $countsQuery->pluck('c', 'abogado_id'); // e.g. [1 => 3, 5 => 2, ...]

    // 2) Trae los abogados paginados (sin subselects)
    $abTable = (new Abogado)->getTable();

    $paginator = Abogado::query()
        ->select("$abTable.*")
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

    // 3) Inyecta el count desde el array $counts a cada fila
    $paginator->getCollection()->transform(function ($a) use ($counts) {
        return [
            'id'            => $a->id,
            'nombre'        => $a->nombre,
            'estatus'       => $a->estatus,
            'usuario_id'    => $a->usuario_id,
            'juicios_count' => (int) ($counts[$a->id] ?? 0), // 👈 aquí forzamos el conteo correcto
        ];
    });

    // Descomenta 1 vez para verificar que llega bien:
    // dd($paginator->toArray()['data'][0]);

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
