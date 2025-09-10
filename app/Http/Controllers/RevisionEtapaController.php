<?php
// app/Http/Controllers/RevisionEtapaController.php
namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\RevisionEtapa;
use App\Models\Abogado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class RevisionEtapaController extends Controller
{
  public function index(Revision $revision)
  {
    $revision->load(['cliente:id,nombre']);
    $etapas = $revision->etapas()->with(['abogado:id,nombre','usuario:id,name'])->get();

    return Inertia::render('Revisiones/Etapas', [
      'revision' => $revision,
      'etapas'   => $etapas,
      'abogados' => Abogado::orderBy('nombre')->get(['id','nombre']),
      'sugeridas'=> [
        ['orden'=>1,'nombre'=>'Atención de primer requerimiento'],
        ['orden'=>2,'nombre'=>'Solicitud de prórroga'],
        ['orden'=>3,'nombre'=>'Entrega de documentación'],
      ],
    ]);
  }

  public function store(Request $request, Revision $revision)
  {
    $data = $request->validate([
      'orden'            => ['required','integer','min:1'],
      'nombre'           => ['required','string','max:255'],
      'fecha_inicio'     => ['nullable','date'],
      'dias_vencimiento' => ['nullable','integer','min:1'],
      'comentarios'      => ['nullable','string'],
      'estatus'          => ['required','in:pendiente,atendido,en_proceso,cerrado'],
      'abogado_id'       => ['nullable','exists:abogados,id'],
    ]);

    $data['usuario_id'] = auth()->id();

    // calcula fecha de vencimiento si aplica
    if (!empty($data['fecha_inicio']) && !empty($data['dias_vencimiento'])) {
      $data['vence'] = Carbon::parse($data['fecha_inicio'])->addDays((int)$data['dias_vencimiento']);
    }

    $revision->etapas()->create($data);

    return back()->with('success','Etapa creada.');
  }

  public function update(Request $request, Revision $revision, RevisionEtapa $etapa)
  {
    $data = $request->validate([
      'orden'            => ['required','integer','min:1'],
      'nombre'           => ['required','string','max:255'],
      'fecha_inicio'     => ['nullable','date'],
      'dias_vencimiento' => ['nullable','integer','min:1'],
      'comentarios'      => ['nullable','string'],
      'estatus'          => ['required','in:pendiente,atendido,en_proceso,cerrado'],
      'abogado_id'       => ['nullable','exists:abogados,id'],
    ]);

    if (!empty($data['fecha_inicio']) && !empty($data['dias_vencimiento'])) {
      $data['vence'] = \Illuminate\Support\Carbon::parse($data['fecha_inicio'])->addDays((int)$data['dias_vencimiento']);
    } else {
      $data['vence'] = null;
    }

    $etapa->update($data);

    return back()->with('success','Etapa actualizada.');
  }

  public function destroy(Revision $revision, RevisionEtapa $etapa)
  {
    $etapa->delete();
    return back()->with('success','Etapa eliminada.');
  }
}
