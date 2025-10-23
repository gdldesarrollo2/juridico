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
use App\Support\JuicioAbogadoService;

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
        'q'            => ['nullable','string','max:255'],
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

    if (!empty($filters['q'])) {
        $q = $filters['q'];
        $query->where(function($qq) use ($q) {
            $qq->where('nombre', 'like', "%{$q}%")
               ->orWhere('numero_juicio', 'like', "%{$q}%")
               ->orWhere('numero_expediente', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['cliente_id'])) {
        $query->where('cliente_id', $filters['cliente_id']);
    }

    if (!empty($filters['estatus'])) {
        $query->where('estatus', $filters['estatus']);
    }

    if (!empty($filters['fecha_desde'])) {
        $query->whereDate('fecha_inicio', '>=', $filters['fecha_desde']);
    }
    if (!empty($filters['fecha_hasta'])) {
        $query->whereDate('fecha_inicio', '<=', $filters['fecha_hasta']);
    }

    if (!empty($filters['etiqueta_id'])) {
        $query->whereHas('etiquetas', function($qq) use ($filters) {
            $qq->where('etiquetas.id', $filters['etiqueta_id']);
        });
    }

    $sort = $filters['sort'] ?? '-fecha_inicio';
    [$col,$dir] = str_starts_with($sort,'-') ? [substr($sort,1),'desc'] : [$sort,'asc'];
    $allowed = ['fecha_inicio','created_at','monto'];
    if (!in_array($col,$allowed)) { $col = 'fecha_inicio'; $dir = 'desc'; }
    $query->orderBy($col, $dir);

    $juicios = $query->paginate(10)->withQueryString();

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
        'juicios'   => $juicios,   // ← aquí cada juicio trae periodo_resumen
        'filters'   => $filters,
        'catalogos' => $catalogos,
    ]);
}


    /**
     * Mostrar formulario de creación de Juicio
     */
   public function create()
{
    $clientes    = Cliente::select('id','nombre')->orderBy('nombre')->get();
    $autoridades = Autoridad::select('id','nombre')->orderBy('nombre')->get();
    $abogados    = Abogado::select('id','nombre')->where('estatus', 'activo')->orderBy('nombre')->get();
    $etiquetas   = Etiqueta::select('id','nombre')->orderBy('nombre')->get();

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
        'nombre'               => ['required','string','max:255'],
        'tipo'                 => ['required','in:nulidad,revocacion'],
        'cliente_id'           => ['required','exists:clientes,id'],
        'autoridad_id'         => ['required','exists:autoridades,id'],
        'fecha_inicio'         => ['required','date'],
        'monto'                => ['required','numeric','min:0'],
        'observaciones_monto'  => ['required','string'],
        'resolucion_impugnada' => ['required','string','max:255'],
        'garantia'             => ['required','string','max:255'],
        'numero_juicio'        => ['required','string','max:255'],
        'numero_expediente'    => ['required','string','max:255'],
        'estatus'              => ['required','in:juicio,autorizado,en_proceso,concluido'],
        'abogado_id'           => ['required','exists:abogados,id'],
        // ← NUEVO
        'periodos'              => ['required','array','min:1'],
        'periodos.*.anio'       => ['required','integer','between:2000,2100','distinct'],
        'periodos.*.meses'      => ['required','array','min:1'],
        'periodos.*.meses.*'   => ['required', 'integer', 'between:1,12']
        ]);

    // normaliza a mapa {"2024":[1,2], "2025":[3,4]}
    $periodosMapa = [];
    foreach ($validated['periodos'] as $p) {
        $anio  = (string) $p['anio'];
        $meses = array_values(array_unique(array_map('intval', $p['meses'])));
        sort($meses);
        $periodosMapa[$anio] = $meses;
    }

    $data = $validated;
    $data['periodos'] = $periodosMapa;
    $abogadoId = $validated['abogado_id'] ?? null;
    // si quieres deprecar fecha_inicio:
    // $data['fecha_inicio'] = null;

    $juicio = Juicio::create($data);
    JuicioAbogadoService::setAbogado($juicio, $abogadoId, $request->user()?->id, 'Asignación inicial');

    return redirect()->route('juicios.index')
        ->with('success', 'Juicio creado correctamente.');
}

 // Formulario de edición
public function edit(Juicio $juicio)
{
    $juicio->load([]); // si necesitas relaciones

    return Inertia::render('Juicios/Edit', [
        'juicio' => $juicio, // 👈 así no se te “pierde” ningún campo
        'clientes' => Cliente::select('id','nombre')->orderBy('nombre')->get(),
        'autoridades' => Autoridad::select('id','nombre')->orderBy('nombre')->get(),
        'abogados' => Abogado::select('id','nombre')->orderBy('nombre')->get(), // ya normalizado
        'etiquetas' => Etiqueta::select('id','nombre')->orderBy('nombre')->get(),
        'etiquetasSeleccionadas' => $juicio->etiquetas()->pluck('etiquetas.id'),
    ]);
}

    // Actualización (datos base + pivot etiquetas)
    public function update(Request $request, Juicio $juicio)
{
    $validated = $request->validate([
        'nombre'               => ['required','string','max:255'],
        'tipo'                 => ['required','in:nulidad,revocacion'],
        'cliente_id'           => ['required','exists:clientes,id'],
        'autoridad_id'         => ['nullable','exists:autoridades,id'],
        'fecha_inicio'         => ['nullable','date'],
        'monto'                => ['nullable','numeric','min:0'],
        'observaciones_monto'  => ['nullable','string'],
        'resolucion_impugnada' => ['nullable','string','max:255'],
        'garantia'             => ['nullable','string','max:255'],
        'numero_juicio'        => ['nullable','string','max:255'],
        'numero_expediente'    => ['nullable','string','max:255'],
        'estatus'              => ['required','in:juicio,autorizado,en_proceso,concluido'],
        'abogado_id'           => ['nullable','exists:abogados,id'],
        // ← NUEVO
        'periodos'              => ['required','array','min:1'],
        'periodos.*.anio'       => ['required','integer','between:2000,2100','distinct'],
        'periodos.*.meses'      => ['required','array','min:1'],
        'periodos.*.meses.*'    => ['integer','between:1,12','distinct'],
    ]);

    $periodosMapa = [];
    foreach ($validated['periodos'] as $p) {
        $anio  = (string) $p['anio'];
        $meses = array_values(array_unique(array_map('intval', $p['meses'])));
        sort($meses);
        $periodosMapa[$anio] = $meses;
    }
     $nuevoAbogado = $validated['abogado_id'] ?? null;

    $data = $validated;
    $data['periodos'] = $periodosMapa;

    $juicio->fill($validated);
    $juicio->save();
    if ($juicio->abogado_id != $nuevoAbogado) {
        JuicioAbogadoService::setAbogado($juicio, $nuevoAbogado, $request->user()?->id, 'Reasignación por edición');
    }
    return redirect()->route('juicios.index')
        ->with('success', 'Juicio actualizado correctamente.');
}
public function show(Juicio $juicio)
{
    // Cargar info general
    $juicio->load([
        'cliente:id,nombre',
        'autoridad:id,nombre',
        'abogado:id,nombre',
        'etiquetas:id,nombre',
    ]);

    // Formatear periodo (si guardas JSON con {año:[meses]})
    $periodoStr = $this->formatPeriodos($juicio->periodos);

    // Etapas
    $etapas = $juicio->etapas()
        ->with([
            'etiqueta:id,nombre',
        ])
        ->get([
            'id','etiqueta_id','etapa','abogado_id','rol','comentarios',
            'dias_vencimiento','fecha_inicio','fecha_vencimiento','estatus','archivo_path','created_at'
        ]);

    // Historial de abogados
    $historial = $juicio->abogadosHistorial()
        ->with([
            'abogado:id,nombre',
        ])
        ->get([
            'id','juicio_id','abogado_id','abogado_id',
            'motivo','asignado_desde','asignado_hasta','created_at'
        ]);

    // Empaquetar props
    return Inertia::render('Juicios/Show', [
        'juicio' => [
            'id'                => $juicio->id,
            'numero_juicio'     => $juicio->numero_juicio,
            'numero_expediente' => $juicio->numero_expediente,
            'nombre'            => $juicio->nombre,
            'tipo'              => $juicio->tipo,
            'cliente'           => optional($juicio->cliente)->nombre,
            'autoridad'         => optional($juicio->autoridad)->nombre,
            'abogado'           => optional($juicio->abogado)->nombre,
            'estatus'           => $juicio->estatus,
            'monto'             => $juicio->monto,
            'fecha_inicio'      => $juicio->fecha_inicio,
            'observaciones_monto' => $juicio->observaciones_monto,
            'resolucion_impugnada'=> $juicio->resolucion_impugnada,
            'garantia'            => $juicio->garantia,
            'etiquetas'         => $juicio->etiquetas->pluck('nombre'),
            'periodo'           => $periodoStr, // “2024: Ene, Feb | 2025: Mar, Abr”
        ],
        'etapas'    => $etapas,
        'historial' => $historial,
    ]);
}

/**
 * Convierte el JSON de periodos a texto bonito.
 * Ej: {"2024":[1,2],"2025":[3,4]} => "2024: Ene, Feb | 2025: Mar, Abr"
 */
private function formatPeriodos($periodos): ?string
{
    if (!$periodos) return null;
    if (is_string($periodos)) {
        $periodos = json_decode($periodos, true);
    }
    if (!is_array($periodos)) return null;

    $meses = [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $bloques = [];
    foreach ($periodos as $anio => $arr) {
        $nombres = collect($arr)->sort()->map(fn($m) => $meses[(int)$m] ?? $m)->implode(', ');
        $bloques[] = "$anio: $nombres";
    }
    return implode(' | ', $bloques);
}


}
