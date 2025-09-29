<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\RevisionEtapa;
use App\Models\Abogado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RevisionEtapaController extends Controller
{
    public function index(Revision $revision)
    {
        // catálogos (ajusta a tus tablas reales)
        $catalogoEtapas = \DB::table('catalogo_etapas')
            ->select('id','nombre')
           ->get();

        $estatus = ['PENDIENTE','ATENDIDO','CANCELADO'];

        $abogados = Abogado::query()
            ->select('id','nombre')
            ->orderBy('nombre')
            ->get();

        // historial de etapas
        $etapas = $revision->etapas()
            ->with('usuarioCaptura:id,name')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(function ($e){
                return [
                    'id' => $e->id,
                    'nombre' => $e->nombre, // NUEVO
                    'etapa' => $e->catalogo_etapa_id, // el componente lo resolverá a nombre
                    'fecha_inicio' => optional($e->fecha_inicio)->format('d/m/Y'),
                    'fecha_vencimiento' => optional($e->fecha_vencimiento)->format('d/m/Y'),
                    'estatus' => $e->estatus,
                    'comentarios' => $e->comentarios,
                    'usuario' => optional($e->usuarioCaptura)->name
                ];
            });

        return Inertia::render('RevisionEtapas/CrearEtapa', [
            'revision' => [
                'id' => $revision->id,
                'empresa' => $revision->empresa,
            ],
            'catalogoEtapas' => $catalogoEtapas,
            'estatus' => $estatus,
            'abogados' => $abogados,
            'historial' => $etapas,
        ]);
    }

    public function store(Request $request, Revision $revision)
    {
        $tipos = ['gabinete','domiciliaria','electronica','secuencial'];

    // 1) Tipo
        $vTipo = $request->validate([
            'tipo_revision' => ['required', Rule::in($tipos)],
        ]);

    // 2) Opciones dependientes
        $opciones = self::CATALOGO[$vTipo['tipo_revision']] ?? [];
        $data = $request->validate([
            'nombre'             => ['required','string','max:255'], // NUEVO
            'catalogo_etapa_id'  => ['required','integer','exists:catalogo_etapas,id'],
            'fecha_inicio'       => ['required','date'],
            'dias_vencimiento'   => ['required','integer','min:0','max:3650'],
            'comentarios'        => ['nullable','string','max:2000'],
            'estatus'            => ['required', Rule::in(['PENDIENTE','ATENDIDO','CANCELADO'])],
            'abogado_id'         => ['nullable','integer','exists:users,id'],
        ]);

        $inicio = Carbon::parse($data['fecha_inicio']);
        $vence  = (clone $inicio)->addDays($data['dias_vencimiento']);

        RevisionEtapa::create([
            'revision_id'         => $revision->id,
            'nombre'              => $data['nombre'] ?? null, // NUEVO
            'catalogo_etapa_id'   => $data['catalogo_etapa_id'],
            'fecha_inicio'        => $inicio,
            'dias_vencimiento'    => $data['dias_vencimiento'],
            'fecha_vencimiento'   => $vence,
            'comentarios'         => $data['comentarios'] ?? null,
            'estatus'             => $data['estatus'],
            'abogado_id'          => $data['abogado_id'] ?? null,
            'usuario_captura_id'  => $request->user()->id,
        ]);

        return back()->with('success','Etapa registrada');
    }

    public function update(Request $request, RevisionEtapa $etapa)
    {
        $data = $request->validate([
            'nombre'       => ['sometimes','string','max:255'], // opcional en update
            'estatus'      => ['required', Rule::in(['PENDIENTE','ATENDIDO','CANCELADO'])],
            'comentarios'  => ['nullable','string','max:2000'],
        ]);

        $etapa->update($data);

        return back()->with('success','Etapa actualizada');
    }
}
