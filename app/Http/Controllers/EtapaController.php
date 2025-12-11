<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Juicio;
use App\Models\Etapa;         // Modelo de la tabla etapas del juicio (ajusta el nombre si es distinto)
use App\Models\Etiqueta;
use App\Models\User;          // o Abogado/Usuario según tu app
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class EtapaController extends Controller
{
    // Catálogo fijo: etapas permitidas por tipo de juicio
    private const CATALOGO_ETAPAS = [
        'nulidad' => [
            'Presentación de la demanda',
            'Admisión de la demanda',
            'Suspensión provisional condicionada a garantía',
            'Suspensión definitiva condicionada a garantía',
            'Suspensión definitiva con garantía o por embargo',
            'Contestación de la demanda',
            'Ampliación de demanda',
            'Contestación de ampliación de demanda',
            'Incidente en trámite',
            'Alegatos y cierre de instrucción',
            'Pendiente de sentencia',
            'Sentencia con nulidad lisa y llana',
            'Sentencia con nulidad para efectos',
            'Sentencia de validez',
            'Amparo directo en trámite',
            'Recurso de revisión fiscal en trámite',
            'Revisión adhesiva presentada',
            'En cumplimiento de sentencia',
            'Pendiente de devolución de pago de lo indebido',
        ],
        'revocacion' => [
            'Presentación de recurso de revocación',
            'Requerimiento de pruebas',
            'Requerimiento para acreditar personalidad',
            'Requerimiento de agravios',
            'Resolución que confirma legalidad del acto',
            'Resolución que revoca para efectos',
            'Resolución que revoca el acto totalmente',
        ],
    ];

    private function etapasPorTipo(string $tipo): array
    {
        return self::CATALOGO_ETAPAS[$tipo] ?? [];
    }
    public function lista(Request $request)
{
    $query = Etapa::with(['juicio.cliente'])
        ->orderBy('fecha_vencimiento', 'asc');

    // FILTROS
    if ($request->filled('desde')) {
        $query->whereDate('fecha_vencimiento', '>=', $request->desde);
    }

    if ($request->filled('hasta')) {
        $query->whereDate('fecha_vencimiento', '<=', $request->hasta);
    }

    if ($request->filled('estatus')) {
        $query->where('estatus', $request->estatus);
    }

    if ($request->filled('juicio')) {
        $query->whereHas('juicio', function ($q) use ($request) {
            $q->where('nombre', 'like', "%{$request->juicio}%");
        });
    }

    if ($request->filled('cliente')) {
        $query->whereHas('juicio.cliente', function ($q) use ($request) {
            $q->where('id', $request->cliente);
        });
    }

    return Inertia::render('Etapas/Lista', [
        'etapas'  => $query->paginate(30)->withQueryString(),
        'filters' => $request->only('desde', 'hasta', 'estatus', 'juicio', 'cliente'),
        'clientes' => \App\Models\Cliente::orderBy('nombre')->get(['id','nombre']),
    ]);
}

    public function panel(Request $request)
    {
     // 1) Base de la consulta
    $query = Etapa::with(['juicio.cliente'])
        ->orderBy('fecha_vencimiento', 'asc');

    // 2) FILTROS
    if ($request->filled('desde')) {
        $query->whereDate('fecha_vencimiento', '>=', $request->desde);
    }

    if ($request->filled('hasta')) {
        $query->whereDate('fecha_vencimiento', '<=', $request->hasta);
    }

    if ($request->filled('estatus')) {
        $query->where('estatus', $request->estatus);
    }

    if ($request->filled('juicio')) {
        $query->whereHas('juicio', function ($q) use ($request) {
            $q->where('nombre', 'like', "%{$request->juicio}%");
        });
    }

    if ($request->filled('cliente')) {
        $query->whereHas('juicio.cliente', function ($q) use ($request) {
            $q->where('id', $request->cliente);
        });
    }

    // 3) Ejecutamos la consulta con paginación
    $etapas = $query->paginate(30)->withQueryString();

    // 4) Transformamos cada registro para enviar sólo lo que necesita Vue
    $etapas->getCollection()->transform(function ($etapa) {
        return [
            'id'                => $etapa->id,
            'juicio_id'         => $etapa->juicio_id,
            'etapa'             => $etapa->etapa,
            'fecha_inicio'      => $etapa->fecha_inicio,
            'fecha_vencimiento' => $etapa->fecha_vencimiento,
            'estatus'           => $etapa->estatus,
            'comentarios'       => $etapa->comentarios,
            'archivo_path'      => $etapa->archivo_path,

            // 👇 Estos dos son los que te interesan para las columnas
            'nombre_juicio'  => optional($etapa->juicio)->nombre,
            'nombre_cliente' => optional(optional($etapa->juicio)->cliente)->nombre,
        ];
    });

    // 5) Devolvemos a Inertia
    return Inertia::render('Etapas/Lista', [
        'etapas'   => $etapas,
        'filters'  => $request->only('desde', 'hasta', 'estatus', 'juicio', 'cliente'),
        'clientes' => Cliente::orderBy('nombre')->get(['id', 'nombre']),
    ]);
    }

    public function index(Juicio $juicio)
    {
        // Cátalogos para selects del formulario (como tu vista solicita)
         $catalogoAbogados  = Abogado::select('id','nombre')->orderBy('nombre')->get();

        // Estatus que usa tu UI
        $estatuses = collect([
            ['value' => 'en_tramite', 'label' => 'En trámite'],
            ['value' => 'en_juicio',  'label' => 'En juicio'],
            ['value' => 'concluido',  'label' => 'Concluido'],
            ['value' => 'cancelado',  'label' => 'Cancelado'],
        ]);

        // Listado de etapas ya capturadas
        $etapas = Etapa::query()
            ->with([
                'etiqueta:id,nombre',
                'abogado:id,nombre',
            ])
            ->where('juicio_id', $juicio->id)
            ->orderByDesc('fecha_vencimiento')
            ->get()
            ->map(fn($e) => [
                'id'                => $e->id,
                'etiqueta'          => $e->relationLoaded('etiqueta') ? $e->etiqueta : null,
                'etapa'             => $e->etapa,
                'abogado'           => $e->relationLoaded('abogado') ? $e->abogado : null,
                'rol'               => $e->rol,
                'comentarios'       => $e->comentarios,
                'dias_vencimiento'  => (int) $e->dias_vencimiento,
                'fecha_vencimiento' => optional($e->fecha_vencimiento)->format('Y-m-d'),
                'estatus'           => $e->estatus,
                'archivo_path'      => $e->archivo_path,
                'created_at'        => optional($e->created_at)->toISOString(),
            ]);

       return Inertia::render('Etapas/Index', [
    'juicio' => [
        'id'   => $juicio->id,
        'tipo' => $juicio->tipo,
        'cliente' => $juicio->cliente?->only(['id','nombre']),
    ],
    'fecha_inicio_juicio' => optional($juicio->fecha_inicio)->format('Y-m-d'),
    'catalogos' => [
        'etiquetas' => Etiqueta::select('id','nombre')->orderBy('nombre')->get(),
        'abogados'  => Abogado::select('id','nombre')->where('estatus', 'activo')->orderBy('nombre')->get(),
        'estatuses' => [
            ['value'=>'en_tramite','label'=>'En trámite'],
            ['value'=>'en_juicio','label'=>'En juicio'],
            ['value'=>'concluido','label'=>'Concluido'],
            ['value'=>'cancelado','label'=>'Cancelado'],
        ],
    ],
    'catalogoEtapas' => self::CATALOGO_ETAPAS,   // nulidad / revocacion
    'etapas' => $etapas,                         // tu colección mapeada
]);

    }

    public function store(Request $request, Juicio $juicio)
    {
        // Etapas válidas según el tipo del juicio
        $etapasValidas = $this->etapasPorTipo($juicio->tipo);

        $data = $request->validate([
            'etiqueta_id'       => ['nullable','integer','exists:etiquetas,id'],
            'etapa'             => ['required','string', Rule::in($etapasValidas)],
            'abogado_id'        => ['nullable','integer','exists:abogados,id'], // ajusta a tu tabla si no es users
            'rol'               => ['nullable','string','max:100'],
            'comentarios'       => ['nullable','string'],
            'fecha_inicio'      => ['required','date'],
            'dias_vencimiento'  => ['required','integer','min:0','max:3650'],
            'fecha_vencimiento' => ['nullable','date'],
            'estatus'           => ['required', Rule::in(['en_tramite','en_juicio','concluido','cancelado'])],
            'archivo'           => ['nullable','file','max:10240'], // 10MB; añade mimes:pdf,docx,png,jpg si quieres
        ], [
            'etapa.in' => 'La etapa seleccionada no es válida para el tipo de juicio '.$juicio->tipo.'.',
        ]);

        // Si no envían fecha_vencimiento, la calculamos como fecha_inicio + días corridos
        $fechaInicio = Carbon::parse($data['fecha_inicio']);
        $fechaVenc   = $data['fecha_vencimiento']
            ? Carbon::parse($data['fecha_vencimiento'])
            : (clone $fechaInicio)->addDays((int)$data['dias_vencimiento']);

        // Subida de archivo (opcional)
        $archivoPath = null;
        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('etapas', 'public'); // storage/app/public/etapas
        }

        // Crear etapa
        Etapa::create([
            'juicio_id'         => $juicio->id,
            'etiqueta_id'       => $data['etiqueta_id'] ?: null,
            'etapa'             => $data['etapa'],
            'abogado_id'        => $data['abogado_id'] ?: null,
            'rol'               => $data['rol'] ?: null,
            'comentarios'       => $data['comentarios'] ?: null,
            'fecha_inicio'      => $fechaInicio->format('Y-m-d'),
            'dias_vencimiento'  => (int)$data['dias_vencimiento'],
            'fecha_vencimiento' => $fechaVenc->format('Y-m-d'),
            'estatus'           => $data['estatus'],
            'archivo_path'      => $archivoPath,
            'usuario_captura_id'=> $request->user()?->id, // si tienes este campo
        ]);

        return redirect()
            ->route('juicios.etapas.index', $juicio->id)
            ->with('success', 'Etapa registrada correctamente.');
    }
}
