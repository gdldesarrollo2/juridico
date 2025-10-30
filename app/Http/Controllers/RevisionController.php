<?php

namespace App\Http\Controllers;

use App\Models\Revision;   // <-- tu modelo
use App\Models\User;       // <-- si usas otro (Abogado), cámbialo
use App\Models\Empresa;   // <-- “empresa”
use App\Models\Autoridad;  // <-- catálogo de autoridades
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
class RevisionController extends Controller
{
    /**
     * Catálogo de opciones por tipo.
     * Claves alineadas a tus columnas: gabinete, domiciliaria, electronica, secuencial.
     */
    private const CATALOGO = [
        'gabinete' => [
            'Orden de revisión de gabinete con requerimiento',
            'Solicitud de prórroga primer requerimiento',
            'Atención de primer requerimiento',
            'Segundo requerimiento',
            'Solicitud de prórroga para segundo requerimiento',
            'Tercer requerimiento',
            'Cuarto requerimiento',
            'Compulsa en desarrollo',
            'Requerimiento a la CNBV u otra autoridad',
            'Atenta invitación, cita dar observaciones',
            'Oficio de Observaciones, plazo 20 días hábiles pruebas',
            'Vencimiento del plazo de 12 meses para concluir',
            'Pendiente de resolución (crédito fiscal)',
            'Resolución definitiva (crédito fiscal)',
            'En recurso de revocación',
            'En juicio de nulidad',
        ],
        'domiciliaria' => [
            'Orden de visita domiciliaria',
            'Acta parcial de inicio con requerimiento',
            'Segunda acta parcial',
            'Tercera acta parcial',
            'Cuarta acta parcial',
            'Compulsa en desarrollo',
            'Requerimiento a la CNBV y otras autoridades',
            'Atenta invitación, cita dar omisiones',
            'Última acta parcial',
            'Acta final',
            'Vencimiento plazo 12 meses para concluir',
            'Pendiente de resolución (crédito fiscal)',
            'Resolución definitiva (crédito fiscal)',
            'En recurso de revocación',
            'En juicio de nulidad',
        ],
        'electronica' => [
            'Preliquidación y resolución provisional',
            'Plazo 15 días para presentar pruebas',
            'Segundo requerimiento',
            'Compulsa en desarrollo',
            'Atenta invitación, cita dar omisiones',
            'Pendiente de resolución definitiva',
            'Vencimiento del plazo de 40 días hábiles',
            'Resolución definitiva (crédito fiscal)',
            'Vencimiento del plazo de 6 meses para concluir',
            'En recurso de revocación',
            'En juicio de nulidad',
        ],
        'secuencial' => [
            'Requerimiento al CPR',
            'Orden de visita domiciliaria secuencial',
            'Acta parcial de inicio con requerimiento',
            'Segunda acta parcial',
            'Tercera acta parcial',
            'Cuarta acta parcial',
            'Compulsa en desarrollo',
            'Requerimiento a la CNBV y otras autoridades',
            'Atenta invitación, cita dar omisiones',
            'Última acta parcial',
            'Acta final',
            'Vencimiento plazo 12 meses para concluir',
            'Pendiente de resolución (crédito fiscal)',
            'Resolución definitiva (crédito fiscal)',
            'En recurso de revocación',
            'En juicio de nulidad',
        ],
    ];

    /** Helpers */
    private function opciones(string $tipo): array
    {
        return self::CATALOGO[$tipo] ?? [];
    }

    private function flags(string $tipo): array
    {
        return [
            'rev_gabinete'     => $tipo === 'gabinete' ? 1 : 0,
            'rev_domiciliaria' => $tipo === 'domiciliaria' ? 1 : 0,
            'rev_electronica'  => $tipo === 'electronica' ? 1 : 0,
            'rev_secuencial'   => $tipo === 'secuencial' ? 1 : 0,
        ];
    }

    private function tipoDesdeFlags(Revision $r): string
    {
        return $r->rev_gabinete     ? 'gabinete'
             : ($r->rev_domiciliaria ? 'domiciliaria'
             : ($r->rev_electronica  ? 'electronica'
             : 'secuencial'));
    }

    /** Listado simple */
  public function index()
{
    // filtros desde la query string
    $filters = request()->only([
        'tipo', 'sociedad_id', 'usuario_id', 'autoridad_id', 'estatus', 'q'
    ]);

    $query = Revision::query()
        ->with([
            'empresa:idempresa,razonsocial',
            'autoridad:id,nombre',
            'usuario:id,name',
            'ultimaEtapa:id,revision_id,nombre,fecha_inicio,fecha_vencimiento,estatus',
        ])
        ->filter($filters)
        ->orderByDesc('id');
    $paginator = $query->paginate(20)->withQueryString();

// Si quieres transformar, puedes usar through como ya lo tenías:
$paginator = $paginator->through(function ($r) {
    return [
        'id'         => $r->id,
        'tipo'       => $r->tipo_revision_legible,   // <- aquí
        'sociedad'   => $r->empresa?->razonsocial,
        'sociedad_id'=> $r->idempresa,
        'autoridad'  => $r->autoridad?->nombre,
        'autoridad_id'=> $r->autoridad_id,
        'periodo'    => $r->periodo_etiqueta,         // <- y aquí
        'estatus'    => $r->estatus,
        'empresa_compulsada' => $r->compulsas,
        'observaciones'      => $r->observaciones,
        'usuario'    => $r->usuario?->name,
        'usuario_id' => $r->usuario_id,
         'ultima_etapa' => $r->ultimaEtapa ? [
            'nombre'            => $r->ultimaEtapa->nombre,
            'fecha_inicio'      => optional($r->ultimaEtapa->fecha_inicio)->toDateString(),
            'fecha_vencimiento' => optional($r->ultimaEtapa->fecha_vencimiento)->toDateString(),
            'estatus'           => $r->ultimaEtapa->estatus,
        ] : null,
    ];
});

    // opciones para selects
    $tipos = [
        ['value' => 'gabinete',    'label' => 'Gabinete'],
        ['value' => 'domiciliaria','label' => 'Domiciliaria'],
        ['value' => 'electronica', 'label' => 'Electrónica'],
        ['value' => 'secuencial',  'label' => 'Secuencial'],
    ];

    $sociedades = Empresa::orderBy('razonsocial')
        ->get(['idempresa as id','razonsocial as nombre']);

    $autoridades = Autoridad::orderBy('nombre')
        ->get(['id','nombre']);

    $usuarios = User::orderBy('name')
        ->get(['id','name']);

    $estatus = [
        ['value' => 'en_juicio', 'label' => 'en juicio'],
        ['value' => 'concluido', 'label' => 'concluido'],
        ['value' => 'cancelado', 'label' => 'cancelado'],
    ];

    return Inertia::render('Revisiones/Index', [
       'revisiones' => [
        'data'  => array_values($paginator->items()), // <<— array real
        'links' => $paginator->linkCollection(),
        // si necesitas meta, puedes enviarla aparte
    ],
    'filters' => $filters,
        'options' => [
            'tipos'       => $tipos,
            'sociedades'  => $sociedades,
            'autoridades' => $autoridades,
            'usuarios'    => $usuarios,
            'estatus'     => $estatus,
        ],
    ]);
}
    /** Form de creación */
    public function create()
    {
         return Inertia::render('Revisiones/Create', [
            'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial']),
            'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
            'catalogoRevision' => self::CATALOGO,
            'defaults'    => [
                'estatus' => 'en_juicio',
                'rev_gabinete' => false,
                'rev_domiciliaria' => false,
                'rev_electronica' => false,
                'rev_secuencial' => false,
            ],
        ]);
    }

    /** Guarda */
    public function store(Request $request)
    {
        $tipos = ['gabinete','domiciliaria','electronica','secuencial'];

        // 1) tipo con CLOSURE (evita validateGabinete)
        $vTipo = $request->validate([
            'tipo_revision' => [
                'required',
                function ($attr, $val, $fail) use ($tipos) {
                    if (!in_array($val, $tipos, true)) {
                        $fail('Tipo de revisión inválido.');
                    }
                },
            ],
        ]);

        // 2) resto condicionado por tipo (también con CLOSURE)
        $opciones = $this->opciones($vTipo['tipo_revision']);

        $validated = $request->validate([
  'idempresa'   => ['required','integer','exists:empresas,idempresa'],
  'usuario_id'   => ['nullable','integer','exists:users,id'],
  'autoridad_id' => ['nullable','integer','exists:autoridades,id'],
  'periodos'              => ['required','array','min:1'],
  'periodos.*.anio'       => ['required','integer','between:2000,2100','distinct'],
  'periodos.*.meses'      => ['required','array','min:1'],
  'periodos.*.meses.*'    => ['integer','between:1,12'],
  'objeto'        => ['nullable','string','max:255'],
  'observaciones' => ['nullable','string'],
  'aspectos'      => ['nullable','string'],
  'compulsas'     => ['nullable','string'],
  'no_juicio'     => ['nullable','alpha_num'],
  'estatus'       => ['required', Rule::in(['en_juicio','concluido','cancelado'])],
]);
        $periodosMapa = [];
        foreach ($validated['periodos'] as $item) {
            $anio = (string)$item['anio'];
            $meses = array_values(array_unique(array_map('intval', $item['meses'])));
            sort($meses);
            $periodosMapa[$anio] = $meses;
        }

       $data = [
  'idempresa'     => $validated['empresa_id'],
  'usuario_id'    => $validated['usuario_id'] ?? auth()->id(),
  'autoridad_id'  => $validated['autoridad_id'] ?? null,
  // 'revision' fuera: ahora se define en las etapas
  'periodos'      => $periodosMapa,
  'objeto'        => $validated['objeto'] ?? null,
  'observaciones' => $validated['observaciones'] ?? null,
  'aspectos'      => $validated['aspectos'] ?? null,
  'compulsas'     => $validated['compulsas'] ?? null,
  'no_juicio'     => $validated['no_juicio'] ?? null,
  'estatus'       => $validated['estatus'],
] + $this->flags($vTipo['tipo_revision']);

        Revision::create($data);

        return redirect()->route('revisiones.index')->with('success', 'Revisión creada.');
    }

    /** Form de edición */
  public function edit(Revision $revision)
{
    $tipo = $revision->rev_gabinete ? 'gabinete'
          : ($revision->rev_domiciliaria ? 'domiciliaria'
          : ($revision->rev_electronica ? 'electronica'
          : 'secuencial'));

    return Inertia::render('Revisiones/Edit', [
        'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial']),
        'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
        'catalogoRevision' => self::CATALOGO,
        // ... (tus catálogos)

        // 👇 Agrega esto:
        'revision' => [
            'id'          => $revision->id,
            'idempresa'   => $revision->idempresa,
            'usuario_id'  => $revision->usuario_id,
            'autoridad_id'=> $revision->autoridad_id,
            'revision'    => $revision->revision,
            'periodos'    => $revision->periodos,
            'objeto'      => $revision->objeto,
            'observaciones'=> $revision->observaciones,
            'aspectos'    => $revision->aspectos,
            'compulsas'   => $revision->compulsas,
            'estatus'     => $revision->estatus,
            'tipo_revision'=> $tipo,
            'no_juicio'   => $revision->no_juicio ?? null,
        ],

        // si ya usas initial, puedes mantenerlo, pero no es necesario si usas `revision`
        // 'initial' => [ ... ],
    ]);
}


    /** Actualiza */
public function update(Request $request, Revision $revision)
{
    // 1) Validación
    $data = $request->validate([
        'idempresa'       => ['required','integer','exists:empresas,idempresa'],
        'autoridad_id'    => ['nullable','integer','exists:autoridades,id'],
        'usuario_id'      => ['nullable','integer','exists:users,id'],

        'rev_gabinete'     => ['sometimes','boolean'],
        'rev_domiciliaria' => ['sometimes','boolean'],
        'rev_electronica'  => ['sometimes','boolean'],
        'rev_secuencial'   => ['sometimes','boolean'],

        'no_juicio'      => ['nullable','string','max:100'],
        'objeto'         => ['nullable','string','max:255'],
        'observaciones'  => ['nullable','string','max:2000'],
        'aspectos'       => ['nullable','string','max:2000'],
        'compulsas'      => ['nullable','string','max:2000'],

        'estatus'        => ['required', Rule::in(['en_juicio','concluido','cancelado'])],

        // periodos puede venir como:
        //  a) objeto: {"2022":[1,6,7], "2025":[1,2]}
        //  b) arreglo: [{ anio: 2022, meses:[1,6,7] }, ...]
        'periodos'       => ['nullable', 'array'],
    ]);

    // 2) Normalizar periodos (valida meses y años; quita duplicados)
    $periodos = $this->normalizePeriodos($request->input('periodos'));

    // 3) Persistencia
    DB::transaction(function () use ($revision, $data, $periodos) {
        $revision->fill([
            'idempresa'        => $data['idempresa'],
            'autoridad_id'     => $data['autoridad_id']   ?? null,
            'usuario_id'       => $data['usuario_id']     ?? null,

            'rev_gabinete'     => (bool) Arr::get($data, 'rev_gabinete', $revision->rev_gabinete),
            'rev_domiciliaria' => (bool) Arr::get($data, 'rev_domiciliaria', $revision->rev_domiciliaria),
            'rev_electronica'  => (bool) Arr::get($data, 'rev_electronica',  $revision->rev_electronica),
            'rev_secuencial'   => (bool) Arr::get($data, 'rev_secuencial',   $revision->rev_secuencial),

            'no_juicio'        => $data['no_juicio']       ?? null,
            'objeto'           => $data['objeto']          ?? null,
            'observaciones'    => $data['observaciones']   ?? null,
            'aspectos'         => $data['aspectos']        ?? null,
            'compulsas'        => $data['compulsas']       ?? null,

            'estatus'          => $data['estatus'],
        ]);

        if (!is_null($periodos)) {
            // guarda como JSON consistente (Eloquent cast array → json)
            $revision->periodos = $periodos;
        }

        $revision->save();
    });

    return back()->with('success', 'Revisión actualizada correctamente.');
}

/**
 * Normaliza el input de periodos a un array asociativo
 *   ej: ["2022" => [1,6,7], "2025" => [1,2,3]]
 * Acepta:
 *   - objeto: {"2022":[1,6,7], "2025":[1,2]}
 *   - arreglo: [{anio:2022, meses:[1,6,7]}, ...]
 * Devuelve null si no se envió nada.
 */
private function normalizePeriodos($input): ?array
{
    if ($input === null) {
        return null;
    }

    $out = [];

    // Caso objeto { "2022":[1,6], "2025":[1,2] }
    if (is_array($input) && Arr::isAssoc($input)) {
        foreach ($input as $anio => $meses) {
            $anio = (string) $anio;
            $meses = is_array($meses) ? $meses : [];
            $limpios = $this->sanitizeMonths($meses);
            if ($limpios) {
                $out[$anio] = $limpios;
            }
        }
    }
    // Caso arreglo [{anio:2022, meses:[...]}]
    elseif (is_array($input)) {
        foreach ($input as $row) {
            if (!is_array($row)) continue;
            $anio = isset($row['anio']) ? (string) $row['anio'] : null;
            $meses = isset($row['meses']) && is_array($row['meses']) ? $row['meses'] : [];
            if ($anio) {
                $limpios = $this->sanitizeMonths($meses);
                if ($limpios) {
                    $out[$anio] = $limpios;
                }
            }
        }
    }

    // Ordena por año asc para consistencia
    if ($out) {
        ksort($out);
    }

    return $out ?: [];
}

/** Valida meses 1..12, ints, únicos, ordenados asc */
private function sanitizeMonths(array $meses): array
{
    $meses = array_map('intval', $meses);
    $meses = array_values(array_unique(array_filter($meses, fn($m) => $m >= 1 && $m <= 12)));
    sort($meses);
    return $meses;
}

    /** Elimina (opcional) */
    public function destroy(Revision $revision)
    {
        $revision->delete();
        return back()->with('success', 'Revisión eliminada.');
    }
}
