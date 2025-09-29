<?php

namespace App\Http\Controllers;

use App\Models\Revision;   // <-- tu modelo
use App\Models\User;       // <-- si usas otro (Abogado), cámbialo
use App\Models\Empresa;   // <-- “empresa”
use App\Models\Autoridad;  // <-- catálogo de autoridades
use Illuminate\Http\Request;
use Inertia\Inertia;

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
    public function index(Request $request)
    {
        $revisiones = Revision::query()
            ->with([
                'empresa:idempresa,razonsocial', // â† importante: idempresa
                'autoridad:id,nombre',
            ])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Revisiones/Index', [
            'revisiones' => $revisiones,
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
            'empresa_id'   => ['required','integer','exists:empresas,idempresa'], // map → idempresa
            'usuario_id'    => ['nullable','integer','exists:users,id'],
            'autoridad_id'  => ['nullable','integer','exists:autoridades,id'],
            'revision'      => [
                'required',
                function ($attr, $val, $fail) use ($opciones) {
                    if (!in_array($val, $opciones, true)) {
                        $fail('La revisión seleccionada no es válida para el tipo.');
                    }
                },
            ],
            'periodo_desde' => ['nullable','date'],
            'periodo_hasta' => ['nullable','date','after_or_equal:periodo_desde'],
            'objeto'        => ['nullable','string','max:255'],
            'observaciones' => ['nullable','string'],
            'aspectos'      => ['nullable','string'],
            'compulsas'     => ['nullable','string'],
             'no_juicio' => ['nullable', 'alpha_num'],
            'estatus'       => [
                'required',
                function ($attr, $val, $fail) {
                    if (!in_array($val, ['en_juicio','concluido','cancelado'], true)) {
                        $fail('Estatus inválido.');
                    }
                },
            ],
        ] + $vTipo);

        $data = [
            'idempresa'     => $validated['empresa_id'],
            'usuario_id'    => $validated['usuario_id'] ?? auth()->id(),
            'autoridad_id'  => $validated['autoridad_id'] ?? null,
            'revision'      => $validated['revision'],
            'periodo_desde' => $validated['periodo_desde'] ?? null,
            'periodo_hasta' => $validated['periodo_hasta'] ?? null,
            'objeto'        => $validated['objeto'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'aspectos'      => $validated['aspectos'] ?? null,
            'compulsas'     => $validated['compulsas'] ?? null,
            'no_juicio'     => $validated['no_juicio'] ?? null,
            'estatus'       => $validated['estatus'],
        ] + $this->flags($validated['tipo_revision']);

        Revision::create($data);

        return redirect()->route('revisiones.index')->with('success', 'Revisión creada.');
    }

    /** Form de edición */
    public function edit(Revision $revision)
    {
        $tipo = $this->tipoDesdeFlags($revision);

        return Inertia::render('Revisiones/Edit', [
            'catalogoRevision' => self::CATALOGO,
             'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial']),
            'autoridades'      => Autoridad::select('id','nombre')->orderBy('nombre')->get(),
            'usuarios'         => User::select('id','name')->orderBy('name')->get(),
            'estatuses'        => [
                ['value' => 'en_juicio',  'label' => 'En juicio'],
                ['value' => 'concluido',  'label' => 'Concluido'],
                ['value' => 'cancelado',  'label' => 'Cancelado'],
            ],
            'initial' => [
                'tipo_revision'  => $tipo,
                'empresa_id'    => $revision->idempresa,
                'usuario_id'     => $revision->usuario_id,
                'autoridad_id'   => $revision->autoridad_id,
                'revision'       => $revision->revision,
                'periodo_desde'  => optional($revision->periodo_desde)->format('Y-m-d'),
                'periodo_hasta'  => optional($revision->periodo_hasta)->format('Y-m-d'),
                'objeto'         => $revision->objeto,
                'observaciones'  => $revision->observaciones,
                'aspectos'       => $revision->aspectos,
                'compulsas'      => $revision->compulsas,
                'no_juicio'      => $revision->no_juicio,
                'estatus'        => $revision->estatus,
            ],
            'revisionId' => $revision->id,
        ]);
    }

    /** Actualiza */
    public function update(Request $request, Revision $revision)
    {
        $tipos = ['gabinete','domiciliaria','electronica','secuencial'];

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

        $opciones = $this->opciones($vTipo['tipo_revision']);

        $validated = $request->validate([
            'empresa_id'   => ['required','integer','exists:empresas,id'],
            'usuario_id'    => ['nullable','integer','exists:users,id'],
            'autoridad_id'  => ['nullable','integer','exists:autoridades,id'],
            'revision'      => [
                'required',
                function ($attr, $val, $fail) use ($opciones) {
                    if (!in_array($val, $opciones, true)) {
                        $fail('La revisión seleccionada no es válida para el tipo.');
                    }
                },
            ],
            'periodo_desde' => ['nullable','date'],
            'periodo_hasta' => ['nullable','date','after_or_equal:periodo_desde'],
            'objeto'        => ['nullable','string','max:255'],
            'observaciones' => ['nullable','string'],
            'aspectos'      => ['nullable','string'],
            'compulsas'     => ['nullable','string'],
            'estatus'       => [
                'required',
                function ($attr, $val, $fail) {
                    if (!in_array($val, ['en_juicio','concluido','cancelado'], true)) {
                        $fail('Estatus inválido.');
                    }
                },
            ],
        ] + $vTipo);

        $revision->update([
            'idempresa'     => $validated['empresa_id'],
            'usuario_id'    => $validated['usuario_id'] ?? auth()->id(),
            'autoridad_id'  => $validated['autoridad_id'] ?? null,
            'revision'      => $validated['revision'],
            'periodo_desde' => $validated['periodo_desde'] ?? null,
            'periodo_hasta' => $validated['periodo_hasta'] ?? null,
            'objeto'        => $validated['objeto'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'aspectos'      => $validated['aspectos'] ?? null,
            'compulsas'     => $validated['compulsas'] ?? null,
            'no_juicio'     => $validated['no_juicio'] ?? null,
            'estatus'       => $validated['estatus'],
        ] + $this->flags($validated['tipo_revision']));

        return redirect()->route('revisiones.index')->with('success', 'Revisión actualizada.');
    }

    /** Elimina (opcional) */
    public function destroy(Revision $revision)
    {
        $revision->delete();
        return back()->with('success', 'Revisión eliminada.');
    }
}
