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
  public function index(Revision $revision)
{
    // 1) Inferir el tipo de revisión desde los flags de la revisión
    $tipo = match (true) {
        (bool) $revision->rev_gabinete     => 'gabinete',
        (bool) $revision->rev_domiciliaria => 'domiciliaria',
        (bool) $revision->rev_electronica  => 'electronica',
        (bool) $revision->rev_secuencial   => 'secuencial',
        default => null,
    };

    // 2) Catálogo dependiente del tipo (usa el que ya tienes en este mismo controlador)
    $lista = self::CATALOGO[$tipo] ?? [];

    // 3) Adaptar a {id, nombre} para el <select>
    $catalogoEtapas = collect($lista)->values()->map(function ($nombre, $i) {
        return ['id' => $i + 1, 'nombre' => $nombre];
    });

    $estatus = ['PENDIENTE','ATENDIDO','CANCELADO'];

    $abogados = Abogado::query()
        ->select('id','nombre')
        ->orderBy('nombre')
        ->get();

    // Historial (como ya lo tenías)
    $etapas = $revision->etapas()
        ->with('usuarioCaptura:id,name')
        ->orderByDesc('fecha_inicio')
        ->get()
        ->map(fn ($e) => [
            'id'                => $e->id,
            'nombre'            => $e->nombre,
            'etapa'             => $e->catalogo_etapa_id, // índice 1..n
            'fecha_inicio'      => optional($e->fecha_inicio)->format('Y-m-d'),
            'fecha_vencimiento' => optional($e->fecha_vencimiento)->format('Y-m-d'),
            'estatus'           => $e->estatus,
            'comentarios'       => $e->comentarios,
            'usuario'           => optional($e->usuarioCaptura)->name,
        ]);

    return Inertia::render('RevisionEtapas/CrearEtapa', [
        'revision'       => [
            'id'   => $revision->id,
            'tipo' => $tipo,  // útil en el front
            'empresa' => $revision->empresa, // si lo usas en el encabezado
        ],
        'catalogoEtapas' => $catalogoEtapas,
        'estatus'        => $estatus,
        'abogados'       => $abogados,
        'historial'      => $etapas,
    ]);
}


  public function store(Request $request, Revision $revision)
{
    // Inferir tipo desde la revisión (no dependas del front)
    $tipo = match (true) {
        (bool) $revision->rev_gabinete     => 'gabinete',
        (bool) $revision->rev_domiciliaria => 'domiciliaria',
        (bool) $revision->rev_electronica  => 'electronica',
        (bool) $revision->rev_secuencial   => 'secuencial',
        default => null,
    };

    $opciones = self::CATALOGO[$tipo] ?? [];
    $max = count($opciones);

   
    $data = $request->validate([
        'catalogo_etapa_id'  => ['required','integer','between:1,'.$max],
        'fecha_inicio'       => ['required','date'],
        'dias_vencimiento'   => ['nullable','integer','min:0'],
        'fecha_vencimiento'  => ['nullable','date'],
        'estatus'            => ['required', Rule::in(['PENDIENTE','ATENDIDO','CANCELADO'])],
        'comentarios'        => ['nullable','string','max:2000'],
        'abogado_id'         => ['nullable','integer','exists:abogados,id'],
    ]);

    // Si no viene, la calculamos
    if (empty($data['fecha_vencimiento']) && isset($data['dias_vencimiento'])) {
        $fi = Carbon::parse($data['fecha_inicio'])->startOfDay();
        $fv = (clone $fi)->addDays((int) $data['dias_vencimiento']);
        $data['fecha_vencimiento'] = $fv->toDateString();
    }

    $nombre = $opciones[$data['catalogo_etapa_id'] - 1] ?? null;

    RevisionEtapa::create([
        'revision_id'        => $revision->id,
        'nombre'             => $nombre,
        'catalogo_etapa_id'  => $data['catalogo_etapa_id'],
        'fecha_inicio'       => $data['fecha_inicio'],
        'dias_vencimiento'   => $data['dias_vencimiento'] ?? null,
        'fecha_vencimiento'  => $data['fecha_vencimiento'] ?? null, // <- se guarda
        'estatus'            => $data['estatus'],
        'comentarios'        => $data['comentarios'] ?? null,
        'abogado_id'         => $data['abogado_id'] ?? null,
        'usuario_captura_id' => auth()->id(),
    ]);

    return back()->with('success','Etapa creada.');

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
