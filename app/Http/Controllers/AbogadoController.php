<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Juicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;  // 👈 agrega esto
use App\Models\JuicioAbogadoHistorial;
use Carbon\Carbon;

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
  public function reasignarStore(Request $request, Abogado $abogado)
{
    $data = $request->validate([
        'nuevo_abogado_id' => ['required','integer','exists:abogados,id'],
        'juicios'          => ['required','array','min:1'],
        'juicios.*'        => ['integer','exists:juicios,id'],
        'motivo'           => ['nullable','string','max:255'],
    ]);

    if ((int)$data['nuevo_abogado_id'] === (int)$abogado->id) {
        return back()->withErrors(['nuevo_abogado_id' => 'El nuevo abogado no puede ser el mismo.']);
    }

    $nuevo = Abogado::findOrFail($data['nuevo_abogado_id']);
    $motivo = $data['motivo'] ?? 'Reasignación de abogado';
    $now    = Carbon::now();
    $userId = auth()->id();

    $juicios = Juicio::whereIn('id', $data['juicios'])
        ->where('abogado_id', $abogado->id)
        ->get();

    if ($juicios->isEmpty()) {
        return back()->withErrors(['juicios' => 'Los juicios seleccionados no pertenecen al abogado actual.']);
    }

    DB::transaction(function () use ($juicios, $abogado, $nuevo, $motivo, $now, $userId) {
        foreach ($juicios as $j) {
            JuicioAbogadoHistorial::where('juicio_id', $j->id)
                ->where('abogado_id', $abogado->id)
                ->whereNull('asignado_hasta')
                ->update([
                    'asignado_hasta' => $now,
                    'changed_by'     => $userId,
                    'motivo'         => 'Cierre por reasignación',
                    'updated_at'     => $now,
                ]);

            $j->update(['abogado_id' => $nuevo->id]);

            JuicioAbogadoHistorial::create([
                'juicio_id'       => $j->id,
                'abogado_id'      => $nuevo->id,
                'usuario_id'      => $nuevo->usuario_id ?? null,
                'asignado_desde'  => $now,
                'asignado_hasta'  => null,
                'changed_by'      => $userId,
                'motivo'          => $motivo,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    });

    return redirect()->route('abogados.index')
        ->with('success', "Se reasignaron {$juicios->count()} juicio(s).");
}

public function reasignarForm(Abogado $abogado)
{
    // Juicios del abogado actual
    $juicios = Juicio::where('abogado_id', $abogado->id)
        ->orderBy('id')
        ->get(['id','nombre']);

    // Candidatos = otros abogados activos (distintos al actual)
    $candidatos = Abogado::query()
        ->where('estatus', 'activo')          // <- ajusta si tus valores son otros
        ->where('id', '!=', $abogado->id)
        ->orderBy('nombre')
        ->get(['id','nombre']);

    return Inertia::render('Abogados/Reasignar', [
        'abogado'    => $abogado->only('id','nombre'),
        'juicios'    => $juicios,
        'candidatos' => $candidatos,
    ]);
}

}
