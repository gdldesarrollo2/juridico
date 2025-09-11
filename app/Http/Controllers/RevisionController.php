<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Empresa;
use App\Models\Autoridad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevisionController extends Controller
{
    /**
     * Listado de revisiones.
     */
    public function index(Request $request)
    {
        $revisiones = Revision::query()
            ->with([
                'empresa:idempresa,razonsocial', // ← importante: idempresa
                'autoridad:id,nombre',
            ])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Revisiones/Index', [
            'revisiones' => $revisiones,
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return Inertia::render('Revisiones/Create', [
            'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial']),
            'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
            'defaults'    => [
                'estatus' => 'en_juicio',
                'rev_gabinete' => false,
                'rev_domiciliaria' => false,
                'rev_electronica' => false,
                'rev_secuencial' => false,
            ],
        ]);
    }

    /**
     * Guardar nueva revisión.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'idempresa'        => ['required','exists:empresas,idempresa'],
            'autoridad_id'     => ['nullable','exists:autoridades,id'],
            'revision'         => ['nullable','string','max:255'],
            'rev_gabinete'     => ['boolean'],
            'rev_domiciliaria' => ['boolean'],
            'rev_electronica'  => ['boolean'],
            'rev_secuencial'   => ['boolean'],
            'periodo_desde'    => ['nullable','date'],
            'periodo_hasta'    => ['nullable','date','after_or_equal:periodo_desde'],
            'objeto'           => ['nullable','string','max:255'],
            'observaciones'    => ['nullable','string'],
            'aspectos'         => ['nullable','string'],
            'compulsas'        => ['nullable','string'],
            'estatus'          => ['required','in:en_juicio,pendiente,autorizado,en_proceso,concluido'],
        ]);

        $data['usuario_id'] = auth()->id();

        Revision::create($data);

        return redirect()->route('revisiones.index')
            ->with('success', 'Revisión creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
  public function edit(Revision $revision)
{
    $revision->load(['empresa:idempresa,razonsocial','autoridad:id,nombre']);

    $payload = [
        'id'              => $revision->id,
        'idempresa'       => (int) $revision->idempresa,
        'autoridad_id'    => $revision->autoridad_id ? (int) $revision->autoridad_id : null,
        'revision'        => $revision->revision,
        'rev_gabinete'    => (bool) $revision->rev_gabinete,
        'rev_domiciliaria'=> (bool) $revision->rev_domiciliaria,
        'rev_electronica' => (bool) $revision->rev_electronica,
        'rev_secuencial'  => (bool) $revision->rev_secuencial,
        'periodo_desde'   => optional($revision->periodo_desde)->toDateString(), // YYYY-MM-DD
        'periodo_hasta'   => optional($revision->periodo_hasta)->toDateString(),
        'objeto'          => $revision->objeto,
        'observaciones'   => $revision->observaciones,
        'aspectos'        => $revision->aspectos,
        'compulsas'       => $revision->compulsas,
        'estatus'         => $revision->estatus,
        'empresa'         => $revision->empresa
            ? ['idempresa' => (int)$revision->empresa->idempresa, 'razonsocial' => $revision->empresa->razonsocial]
            : null,
        'autoridad'       => $revision->autoridad
            ? ['id' => (int)$revision->autoridad->id, 'nombre' => $revision->autoridad->nombre]
            : null,
    ];

    return Inertia::render('Revisiones/Edit', [
        'revision'    => $payload,
        'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial']),
        'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
    ]);
}

    /**
     * Actualizar revisión.
     */
    public function update(Request $request, Revision $revision)
    {
        $data = $request->validate([
            'idempresa'        => ['required','exists:empresas,idempresa'],
            'autoridad_id'     => ['nullable','exists:autoridades,id'],
            'revision'         => ['nullable','string','max:255'],
            'rev_gabinete'     => ['boolean'],
            'rev_domiciliaria' => ['boolean'],
            'rev_electronica'  => ['boolean'],
            'rev_secuencial'   => ['boolean'],
            'periodo_desde'    => ['nullable','date'],
            'periodo_hasta'    => ['nullable','date','after_or_equal:periodo_desde'],
            'objeto'           => ['nullable','string','max:255'],
            'observaciones'    => ['nullable','string'],
            'aspectos'         => ['nullable','string'],
            'compulsas'        => ['nullable','string'],
            'estatus'          => ['required','in:en_juicio,pendiente,autorizado,en_proceso,concluido'],
        ]);

        $data['usuario_id'] = auth()->id();

        $revision->update($data);

        return redirect()->route('revisiones.index')
            ->with('success', 'Revisión actualizada correctamente.');
    }

    /**
     * Eliminar revisión.
     */
    public function destroy(Revision $revision)
    {
        $revision->delete();

        return redirect()->route('revisiones.index')
            ->with('success', 'Revisión eliminada correctamente.');
    }
}
