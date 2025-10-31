<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Empresa;
use App\Models\Autoridad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RevisionController extends Controller
{
    public function index(Request $request)
    {
        // en index()
$filters = $request->only([
    't', 'sociedad_id', 'usuario_id', 'autoridad_id', 'estatus', 'q'
]);

        $query = Revision::withoutGlobalScopes()   // ⬅️ IMPORTANTÍSIMO
    ->with([
        'empresa:idempresa,razonsocial',
        'autoridad:id,nombre',
        'usuario:id,name',
        'ultimaEtapa:id,revision_id,nombre,fecha_inicio',
    ])
    ->filter($filters)
    ->orderByDesc('id');

     

        // 🔎 Descomenta esto si quieres ver el SQL que se genera:
        // dd($filters, $query->toSql(), $query->getBindings());

        $paginator = $query->paginate(20)->withQueryString();

        // Transformamos cada fila en un arreglo plano listo para la vista
        $items = array_values($paginator->through(function (Revision $r) {
            return [
                'id'           => $r->id,
                'tipo'         => $r->tipo_revision_legible, // accessor del modelo
                'sociedad'     => $r->empresa?->razonsocial,
                'sociedad_id'  => $r->idempresa,
                'autoridad'    => $r->autoridad?->nombre,
                'autoridad_id' => $r->autoridad_id,
                'periodo'      => $r->periodo_legible,       // accessor del modelo
                'estatus'      => $r->estatus,
                'empresa_compulsada' => $r->compulsas,
                'observaciones'=> $r->observaciones,
                'usuario'      => $r->usuario?->name,
                'usuario_id'   => $r->usuario_id,
                'ultima_etapa' => $r->ultimaEtapa ? [
                    'nombre'            => $r->ultimaEtapa->nombre,
                    'fecha_inicio'      => optional($r->ultimaEtapa->fecha_inicio)->toDateString(),
                    'fecha_vencimiento' => optional($r->ultimaEtapa->fecha_vencimiento)->toDateString(),
                    'estatus'           => $r->ultimaEtapa->estatus,
                ] : null,
            ];
        })->items());

        // Opciones para los filtros
        $options = [
            'tipos' => [
                ['value' => 'gabinete',    'label' => 'Gabinete'],
                ['value' => 'domiciliaria','label' => 'Domiciliaria'],
                ['value' => 'electronica', 'label' => 'Electrónica'],
                ['value' => 'secuencial',  'label' => 'Secuencial'],
            ],
            'sociedades'  => Empresa::orderBy('razonsocial')->get(['idempresa as id', 'razonsocial as nombre']),
            'autoridades' => Autoridad::orderBy('nombre')->get(['id', 'nombre']),
            'usuarios'    => User::orderBy('name')->get(['id', 'name']),
            'estatus'     => [
                ['value' => 'en_juicio', 'label' => 'en juicio'],
                ['value' => 'concluido', 'label' => 'concluido'],
                ['value' => 'cancelado', 'label' => 'cancelado'],
            ],
        ];

        return Inertia::render('Revisiones/Index', [
            'revisiones' => [
                'data'  => $items,
                'links' => $paginator->linkCollection(),
            ],
            'filters' => $filters,
            'options' => $options,
        ]);
    }

   public function create()
{
    $sociedades = \App\Models\Empresa::orderBy('razonsocial')
        ->get(['idempresa','razonsocial'])
        ->map(fn($e) => ['id' => (int)$e->idempresa, 'nombre' => $e->razonsocial])
        ->values();

    $autoridades = \App\Models\Autoridad::orderBy('nombre')
        ->get(['id','nombre'])
        ->map(fn($a) => ['id' => (int)$a->id, 'nombre' => $a->nombre])
        ->values();

    return \Inertia\Inertia::render('Revisiones/Create', [
        // Si ya usas 'options' mantenlo: sólo asegúrate del shape
        'options' => [
            'sociedades'  => $sociedades,
            'autoridades' => $autoridades,
        ],
        // (opcional) también plano, si lo prefieres
        // 'sociedades'  => $sociedades,
        // 'autoridades' => $autoridades,
    ]);
}


    public function store(Request $request)
    {
        $tipos = ['gabinete','domiciliaria','electronica','secuencial'];

        $request->validate([
            'tipo_revision' => [
                'required',
                function ($attr, $val, $fail) use ($tipos) {
                    if (!in_array($val, $tipos, true)) {
                        $fail('Tipo de revisión inválido.');
                    }
                },
            ],
        ]);

      $validated = $request->validate([
    'idempresa'        => ['required','integer','exists:empresas,idempresa'],
    'usuario_id'       => ['nullable','integer','exists:users,id'],
    'autoridad_id'     => ['nullable','integer','exists:autoridades,id'],

    'periodos'             => ['required','array','min:1'],
    'periodos.*.anio'      => ['required','integer','between:2000,2100','distinct'],
    // ⬇︎ meses ahora son OPCIONALES
    'periodos.*.meses'     => ['nullable','array'],
    'periodos.*.meses.*'   => ['integer','between:1,12'],

    'objeto'          => ['nullable','string','max:255'],
    'observaciones'   => ['nullable','string'],
    'aspectos'        => ['nullable','string'],
    'compulsas'       => ['nullable','string'],
    'no_juicio'       => ['nullable','alpha_num'],
    'estatus'         => ['required', Rule::in(['en_juicio','concluido','cancelado'])],
]);


        $periodosMapa = $this->normalizePeriodos($validated['periodos']);

        $data = [
            'idempresa'      => $validated['idempresa'],
            'usuario_id'     => $validated['usuario_id'] ?? auth()->id(),
            'autoridad_id'   => $validated['autoridad_id'] ?? null,
            'periodos'       => $periodosMapa,
            'objeto'         => $validated['objeto'] ?? null,
            'observaciones'  => $validated['observaciones'] ?? null,
            'aspectos'       => $validated['aspectos'] ?? null,
            'compulsas'      => $validated['compulsas'] ?? null,
            'no_juicio'      => $validated['no_juicio'] ?? null,
            'estatus'        => $validated['estatus'],
        ] + $this->flags($request->string('tipo_revision'));

        Revision::create($data);

        return redirect()->route('revisiones.index')->with('success', 'Revisión creada.');
    }

    public function edit(Revision $revision)
    {
        $revision->load([
            'empresa:idempresa,razonsocial',
            'autoridad:id,nombre',
            'usuario:id,name',
            'ultimaEtapa:id,revision_id,nombre,fecha_inicio,fecha_vencimiento,estatus',
        ]);

        return Inertia::render('Revisiones/Edit', [
            'revision' => [
                'id'            => $revision->id,
                'idempresa'     => $revision->idempresa,
                'autoridad_id'  => $revision->autoridad_id,
                'usuario_id'    => $revision->usuario_id,
                'rev_gabinete'     => (bool) $revision->rev_gabinete,
                'rev_domiciliaria' => (bool) $revision->rev_domiciliaria,
                'rev_electronica'  => (bool) $revision->rev_electronica,
                'rev_secuencial'   => (bool) $revision->rev_secuencial,
                'periodos'       => $revision->periodos ?? [],
                'objeto'         => $revision->objeto,
                'observaciones'  => $revision->observaciones,
                'aspectos'       => $revision->aspectos,
                'compulsas'      => $revision->compulsas,
                'no_juicio'      => $revision->no_juicio,
                'estatus'        => $revision->estatus,
                'ultima_etapa'   => $revision->ultimaEtapa ? [
                    'nombre'            => $revision->ultimaEtapa->nombre,
                    'fecha_inicio'      => optional($revision->ultimaEtapa->fecha_inicio)->toDateString(),
                    'fecha_vencimiento' => optional($revision->ultimaEtapa->fecha_vencimiento)->toDateString(),
                    'estatus'           => $revision->ultimaEtapa->estatus,
                ] : null,
            ],
            'options' => [
                'tipos' => [
                    ['value' => 'gabinete',    'label' => 'Gabinete'],
                    ['value' => 'domiciliaria','label' => 'Domiciliaria'],
                    ['value' => 'electronica', 'label' => 'Electrónica'],
                    ['value' => 'secuencial',  'label' => 'Secuencial'],
                ],
                'sociedades'  => Empresa::orderBy('razonsocial')->get(['idempresa as id', 'razonsocial as nombre']),
                'autoridades' => Autoridad::orderBy('nombre')->get(['id', 'nombre']),
                'usuarios'    => User::orderBy('name')->get(['id', 'name']),
                'estatus'     => [
                    ['value' => 'en_juicio', 'label' => 'en juicio'],
                    ['value' => 'concluido', 'label' => 'concluido'],
                    ['value' => 'cancelado', 'label' => 'cancelado'],
                ],
            ],
        ]);
    }

    public function update(Request $request, Revision $revision)
    {
        $data = $request->validate([
            'idempresa'       => ['nullable','integer','exists:empresas,idempresa'],
            'sociedad_id'     => ['nullable','integer','exists:empresas,idempresa'],
            'autoridad_id'    => ['nullable','integer','exists:autoridades,id'],
            'usuario_id'      => ['nullable','integer','exists:users,id'],

            'rev_gabinete'     => ['sometimes','boolean'],
            'rev_domiciliaria' => ['sometimes','boolean'],
            'rev_electronica'  => ['sometimes','boolean'],
            'rev_secuencial'   => ['sometimes','boolean'],

            'periodos'             => ['required','array','min:1'],
            'periodos.*.anio'      => ['required','integer','between:2000,2100','distinct'],
         // ⬇️ Meses ahora son OPCIONALES
            'periodos.*.meses'     => ['nullable','array'],
            'periodos.*.meses.*'   => ['integer','between:1,12'],


            'objeto'         => ['nullable','string','max:255'],
            'observaciones'  => ['nullable','string'],
            'aspectos'       => ['nullable','string'],
            'compulsas'      => ['nullable','string'],
            'no_juicio'      => ['nullable','alpha_num'],
            'estatus'        => ['required', Rule::in(['en_juicio','concluido','cancelado'])],
        ]);

        $empresaId = $request->integer('idempresa') ?: $request->integer('sociedad_id');
        if (!$empresaId) {
            return back()->withErrors(['idempresa' => 'Debes seleccionar la sociedad.'])->withInput();
        }

        $periodos = $request->filled('periodos') ? $this->normalizePeriodos($request->input('periodos')) : null;

        DB::transaction(function () use ($revision, $data, $empresaId, $periodos) {
            $revision->fill([
                'idempresa'        => $empresaId,
                'autoridad_id'     => $data['autoridad_id']   ?? null,
                'usuario_id'       => $data['usuario_id']     ?? $revision->usuario_id,

                'rev_gabinete'     => array_key_exists('rev_gabinete', $data)     ? (bool)$data['rev_gabinete']     : $revision->rev_gabinete,
                'rev_domiciliaria' => array_key_exists('rev_domiciliaria', $data) ? (bool)$data['rev_domiciliaria'] : $revision->rev_domiciliaria,
                'rev_electronica'  => array_key_exists('rev_electronica', $data)  ? (bool)$data['rev_electronica']  : $revision->rev_electronica,
                'rev_secuencial'   => array_key_exists('rev_secuencial', $data)   ? (bool)$data['rev_secuencial']   : $revision->rev_secuencial,

                'objeto'           => $data['objeto']         ?? $revision->objeto,
                'observaciones'    => $data['observaciones']  ?? $revision->observaciones,
                'aspectos'         => $data['aspectos']       ?? $revision->aspectos,
                'compulsas'        => $data['compulsas']      ?? $revision->compulsas,
                'no_juicio'        => $data['no_juicio']      ?? $revision->no_juicio,
                'estatus'          => $data['estatus'],
            ]);

            if (!is_null($periodos)) {
                $revision->periodos = $periodos;
            }

            $revision->save();
        });

        return back()->with('success', 'Revisión actualizada correctamente.');
    }

    public function destroy(Revision $revision)
    {
        $revision->delete();
        return back()->with('success', 'Revisión eliminada.');
    }

    /* ===================== Helpers privados ===================== */

   private function normalizePeriodos(array $rows): array
{
    $out = [];
    foreach ($rows as $item) {
        $anio  = (string)($item['anio'] ?? null);
        $meses = is_array($item['meses'] ?? null) ? $item['meses'] : [];
        $meses = array_map('intval', $meses);
        $meses = array_values(array_unique(array_filter($meses, fn($m) => $m >= 1 && $m <= 12)));
        sort($meses);
        if ($anio) $out[$anio] = $meses;   // puede ser []
    }
    ksort($out);
    return $out;
}

    private function flags(string $tipo): array
    {
        $tipo = strtolower($tipo);
        return [
            'rev_gabinete'     => $tipo === 'gabinete',
            'rev_domiciliaria' => $tipo === 'domiciliaria',
            'rev_electronica'  => $tipo === 'electronica',
            'rev_secuencial'   => $tipo === 'secuencial',
        ];
    }
}
