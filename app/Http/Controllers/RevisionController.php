<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Empresa;
use App\Models\Autoridad;
use App\Models\Etiqueta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevisionController extends Controller
{
    /**
     * Listado con filtros y paginación.
     */
    public function index(Request $request)
    {
        $q           = $request->input('q');
        $empresa_id  = $request->input('idempresa');
        $etiqueta_id = $request->input('etiqueta_id');
        $estatus     = $request->input('estatus');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $sort        = $request->input('sort', '-periodo_desde');

        $revisiones = Revision::with(['empresa:idempresa,razonsocial', 'autoridad:id,nombre', 'etiquetas:id,nombre'])
            ->when($q, fn($query) =>
                $query->where('revision', 'like', "%{$q}%")
                      ->orWhere('objeto', 'like', "%{$q}%")
            )
            ->when($empresa_id, fn($query) => $query->where('idempresa', $idempresa))
            ->when($etiqueta_id, fn($query) =>
                $query->whereHas('etiquetas', fn($q) => $q->where('id', $etiqueta_id))
            )
            ->when($estatus, fn($query) => $query->where('estatus', $estatus))
            ->when($fecha_desde, fn($query) => $query->whereDate('periodo_desde', '>=', $fecha_desde))
            ->when($fecha_hasta, fn($query) => $query->whereDate('periodo_hasta', '<=', $fecha_hasta))
            ->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Revisiones/Index', [
            'revisiones' => $revisiones,
            'filters'    => $request->only(['q','idempresa','etiqueta_id','estatus','fecha_desde','fecha_hasta','sort']),
            'catalogos'  => [
                'empresas'  => Empresa::orderBy('razonsocial')->get(['idempresa','razonsocial as nombre']),
                'etiquetas' => Etiqueta::orderBy('nombre')->get(['id','nombre']),
                'estatuses' => [
                    ['value' => 'en_juicio',    'label' => 'En juicio'],
                    ['value' => 'pendiente',    'label' => 'Pendiente'],
                    ['value' => 'autorizado',   'label' => 'Autorizado'],
                    ['value' => 'en_proceso',   'label' => 'En proceso'],
                    ['value' => 'concluido',    'label' => 'Concluido'],
                ],
                'sorts' => [
                    ['value' => '-periodo_desde', 'label' => 'Más recientes'],
                    ['value' => 'periodo_desde',  'label' => 'Más antiguos'],
                    ['value' => '-id',            'label' => 'ID descendente'],
                    ['value' => 'id',             'label' => 'ID ascendente'],
                ],
            ],
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return Inertia::render('Revisiones/Create', [
            'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa as id','razonsocial as nombre']),
            'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
            'etiquetas'   => Etiqueta::orderBy('nombre')->get(['id','nombre']),
            'defaults'    => ['estatus' => 'en_juicio'],
        ]);
    }

    /**
     * Guardar nueva revisión.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'idempresa' => ['required', 'exists:empresas,idempresa'],
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
            'etiquetas'        => ['array'],
            'etiquetas.*'      => ['exists:etiquetas,id'],
        ]);

        $data['usuario_id'] = auth()->id();

        $revision = Revision::create($data);

        if (!empty($data['etiquetas'])) {
            $revision->etiquetas()->sync($data['etiquetas']);
        }

        return redirect()->route('revisiones.index')->with('success','Revisión creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Revision $revision)
    {
        $revision->load(['empresa:idempresa,razonsocial','autoridad:id,nombre','etiquetas:id,nombre']);

        return Inertia::render('Revisiones/Edit', [
            'revision'    => $revision,
            'empresas'    => Empresa::orderBy('razonsocial')->get(['idempresa as id','razonsocial as nombre']),
            'autoridades' => Autoridad::orderBy('nombre')->get(['id','nombre']),
            'etiquetas'   => Etiqueta::orderBy('nombre')->get(['id','nombre']),
        ]);
    }

    /**
     * Actualizar revisión.
     */
    public function update(Request $request, Revision $revision)
    {
        $data = $request->validate([
            'empresa_id'       => ['required','exists:empresas,idempresa'],
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
            'etiquetas'        => ['array'],
            'etiquetas.*'      => ['exists:etiquetas,id'],
        ]);

        $revision->update($data);

        if (!empty($data['etiquetas'])) {
            $revision->etiquetas()->sync($data['etiquetas']);
        }

        return redirect()->route('revisiones.index')->with('success','Revisión actualizada correctamente.');
    }

    /**
     * Eliminar revisión.
     */
    public function destroy(Revision $revision)
    {
        $revision->delete();
        return redirect()->route('revisiones.index')->with('success','Revisión eliminada.');
    }
}
