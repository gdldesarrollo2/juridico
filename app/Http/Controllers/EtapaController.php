<?php

namespace App\Http\Controllers;

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

    public function index(Juicio $juicio)
    {
        // Cátalogos para selects del formulario (como tu vista solicita)
        $catalogoEtiquetas = Etiqueta::select('id','nombre')->orderBy('nombre')->get();
        $catalogoUsuarios  = User::select('id','name')->orderBy('name')->get();

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
                'usuario:id,name',
            ])
            ->where('juicio_id', $juicio->id)
            ->orderByDesc('fecha_vencimiento')
            ->get()
            ->map(fn($e) => [
                'id'                => $e->id,
                'etiqueta'          => $e->relationLoaded('etiqueta') ? $e->etiqueta : null,
                'etapa'             => $e->etapa,
                'usuario'           => $e->relationLoaded('usuario') ? $e->usuario : null,
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
        'usuarios'  => User::select('id','name')->orderBy('name')->get(),
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
            'usuario_id'        => ['nullable','integer','exists:users,id'], // ajusta a tu tabla si no es users
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
            'usuario_id'        => $data['usuario_id'] ?: null,
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
