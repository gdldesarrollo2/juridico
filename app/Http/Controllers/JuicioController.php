<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Juicio;
use App\Models\Cliente;
use App\Models\Autoridad;
use App\Models\Abogado;
use App\Models\Etiqueta;
use Illuminate\Support\Facades\DB;   

class JuicioController extends Controller
{
     protected function rules(): array
    {
        return [
            'nombre'               => ['required', 'string', 'max:255'],
            'cliente_id'           => ['required', 'exists:clientes,id'],
            'autoridad_id'         => ['nullable', 'exists:autoridades,id'],
            'fecha_inicio'         => ['nullable', 'date'],
            'monto'                => ['nullable', 'numeric', 'min:0'],
            'observaciones_monto'  => ['nullable', 'string'],
            'resolucion_impugnada' => ['nullable', 'string', 'max:255'],
            'garantia'             => ['nullable', 'string', 'max:255'],
            'numero_juicio'        => ['nullable', 'string', 'max:255'],
            'numero_expediente'    => ['nullable', 'string', 'max:255'],
           // 'estatus'              => ['required', Rule::in(['juicio','autorizado','en_proceso','concluido'])],
            'abogado_id'           => ['nullable', 'exists:abogados,id'],
            'etiquetas'            => ['nullable','array'],
            'etiquetas.*'          => ['integer','exists:etiquetas,id'],
        ];
    }
    /**
     * Listado con filtros y paginación
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q'            => ['nullable','string','max:255'],          // texto libre
            'cliente_id'   => ['nullable','integer','exists:clientes,id'],
            'estatus'      => ['nullable','in:juicio,autorizado,en_proceso,concluido'],
            'fecha_desde'  => ['nullable','date'],
            'fecha_hasta'  => ['nullable','date','after_or_equal:fecha_desde'],
            'etiqueta_id'  => ['nullable','integer','exists:etiquetas,id'],
            'sort'         => ['nullable','in:fecha_inicio,-fecha_inicio,created_at,-created_at,monto,-monto'],
            'page'         => ['nullable','integer','min:1'],
        ]);

        $query = Juicio::query()
            ->with(['cliente:id,nombre','autoridad:id,nombre','abogado:id,nombre','etiquetas:id,nombre']);

        // Texto (nombre / número_juicio / número_expediente)
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function($qq) use ($q) {
                $qq->where('nombre', 'like', "%{$q}%")
                   ->orWhere('numero_juicio', 'like', "%{$q}%")
                   ->orWhere('numero_expediente', 'like', "%{$q}%");
            });
        }

        // Cliente
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        // Estatus
        if (!empty($filters['estatus'])) {
            $query->where('estatus', $filters['estatus']);
        }

        // Rango de fechas (fecha_inicio)
        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_inicio', '>=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_inicio', '<=', $filters['fecha_hasta']);
        }

        // Por etiqueta (pivot)
        if (!empty($filters['etiqueta_id'])) {
            $query->whereHas('etiquetas', function($qq) use ($filters) {
                $qq->where('etiquetas.id', $filters['etiqueta_id']);
            });
        }

        // Orden
        $sort = $filters['sort'] ?? '-fecha_inicio';
        [$col,$dir] = str_starts_with($sort,'-') ? [substr($sort,1),'desc'] : [$sort,'asc'];
        $allowed = ['fecha_inicio','created_at','monto'];
        if (!in_array($col,$allowed)) { $col = 'fecha_inicio'; $dir = 'desc'; }
        $query->orderBy($col, $dir);

        $juicios = $query->paginate(10)->withQueryString();

        // Catálogos para filtros
        $catalogos = [
            'clientes'   => Cliente::orderBy('nombre')->get(['id','nombre']),
            'etiquetas'  => Etiqueta::orderBy('nombre')->get(['id','nombre']),
            'estatuses'  => [
                ['value'=>'juicio','label'=>'JUICIO'],
                ['value'=>'autorizado','label'=>'Autorizado'],
                ['value'=>'en_proceso','label'=>'En proceso'],
                ['value'=>'concluido','label'=>'Concluido'],
            ],
            'sorts' => [
                ['value'=>'-fecha_inicio','label'=>'Fecha inicio ↓'],
                ['value'=>'fecha_inicio','label'=>'Fecha inicio ↑'],
                ['value'=>'-created_at','label'=>'Creación ↓'],
                ['value'=>'created_at','label'=>'Creación ↑'],
                ['value'=>'-monto','label'=>'Monto ↓'],
                ['value'=>'monto','label'=>'Monto ↑'],
            ],
        ];

        return Inertia::render('Juicios/Index', [
            'juicios'   => $juicios,
            'filters'   => $filters,
            'catalogos' => $catalogos,
        ]);
    }

    /**
     * Mostrar formulario de creación de Juicio
     */
   public function create()
{
    $clientes    = \App\Models\Cliente::select('id','nombre')->orderBy('nombre')->get();
    $autoridades = \App\Models\Autoridad::select('id','nombre')->orderBy('nombre')->get();
    $abogados    = \App\Models\Abogado::select('id','nombre')->orderBy('nombre')->get();
    $etiquetas   = \App\Models\Etiqueta::select('id','nombre')->orderBy('nombre')->get();

    return Inertia::render('Juicios/Create', [
        'clientes'    => $clientes,     // <- ARRAY de objetos [{id,nombre},...]
        'autoridades' => $autoridades,
        'abogados'    => $abogados,
        'etiquetas'   => $etiquetas,
    ]);
}

    /**
     * Guardar un nuevo Juicio en la base de datos
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'nombre'               => ['required', 'string', 'max:255'],
        'tipo'                 => ['required', 'in:nulidad,revocacion'],
        'cliente_id'           => ['required', 'exists:clientes,id'],
        'autoridad_id'         => ['nullable', 'exists:autoridades,id'],
        'fecha_inicio'         => ['nullable', 'date'],
        'monto'                => ['nullable', 'numeric', 'min:0'],
        'observaciones_monto'  => ['nullable', 'string'],
        'resolucion_impugnada' => ['nullable', 'string', 'max:255'],
        'garantia'             => ['nullable', 'string', 'max:255'],
        'numero_juicio'        => ['nullable', 'string', 'max:255'],
        'numero_expediente'    => ['nullable', 'string', 'max:255'],
        'estatus'              => ['required', 'in:juicio,autorizado,en_proceso,concluido'],
        'abogado_id'           => ['nullable', 'exists:abogados,id'],
        'etiquetas'            => ['array'],
        'etiquetas.*'          => ['exists:etiquetas,id'],
    ]);

    // separar etiquetas
    $etiquetas = $validated['etiquetas'] ?? [];
    unset($validated['etiquetas']);

    // crear juicio
    $juicio = Juicio::create($validated);

    // guardar en la pivot etiqueta_juicio
    $juicio->etiquetas()->sync($etiquetas);

    return redirect()
        ->route('juicios.index')
        ->with('success', 'Juicio creado correctamente.');
}
 // Formulario de edición
 public function edit(Juicio $juicio)
{
    $clientes    = Cliente::select('id','nombre')->orderBy('nombre')->get();
    $autoridades = Autoridad::select('id','nombre')->orderBy('nombre')->get();
    $abogados    = Abogado::select('id','nombre')->orderBy('nombre')->get();
    $etiquetas   = Etiqueta::select('id','nombre')->orderBy('nombre')->get();

    return Inertia::render('Juicios/Edit', [
        'juicio' => [
            'id' => $juicio->id,                          // ← MUY IMPORTANTE
            'nombre' => $juicio->nombre,
            'tipo' => $juicio->tipo,                       // 'nulidad' | 'revocacion'
            'cliente_id' => $juicio->cliente_id,
            'autoridad_id' => $juicio->autoridad_id,
            'fecha_inicio' => optional($juicio->fecha_inicio)?->format('Y-m-d'), // ← para <input type="date">
            'monto' => $juicio->monto,
            'observaciones_monto' => $juicio->observaciones_monto,
            'resolucion_impugnada' => $juicio->resolucion_impugnada,
            'garantia' => $juicio->garantia,
            'numero_juicio' => $juicio->numero_juicio,
            'numero_expediente' => $juicio->numero_expediente,
            'estatus' => $juicio->estatus,                 // 'juicio' | 'autorizado' | 'en_proceso' | 'concluido'
            'abogado_id' => $juicio->abogado_id,
        ],
        'clientes' => $clientes,
        'autoridades' => $autoridades,
        'abogados' => $abogados,
        'etiquetas' => $etiquetas,
        'etiquetasSeleccionadas' => $juicio->etiquetas()->pluck('etiquetas.id'),
    ]);
}
    // Actualización (datos base + pivot etiquetas)
    public function update(Request $request, Juicio $juicio)
    {
        $validated = $request->validate($this->rules());

        // Separa etiquetas del resto
        $etiquetas = $validated['etiquetas'] ?? null; // null = no tocar; [] = vaciar
        unset($validated['etiquetas']);

        DB::transaction(function () use ($juicio, $validated, $etiquetas) {
            // Actualiza campos simples
            $juicio->fill($validated)->save();

            // Manejo de pivot:
            // - Si viene null: no cambiar etiquetas
            // - Si viene array: sincroniza exactamente esas (incluye vacío para limpiar)
            if (is_array($etiquetas)) {
                $juicio->etiquetas()->sync($etiquetas);
            }
        });

        return redirect()
            ->route('juicios.index')
            ->with('success', 'Juicio actualizado correctamente.');
    }
}
