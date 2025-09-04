<?php

// app/Http/Controllers/EtapaController.php
namespace App\Http\Controllers;

use App\Models\Juicio;
use App\Models\Etiqueta;
use App\Models\Etapa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class EtapaController extends Controller
{
    public function index(Juicio $juicio)
    {
        return Inertia::render('Etapas/Index', [
            'juicio' => $juicio->load('cliente:id,nombre'),
            'catalogos' => [
                'etiquetas' => Etiqueta::orderBy('nombre')->get(['id','nombre']),
                'usuarios'  => User::orderBy('name')->get(['id','name']),
                'estatuses' => [
                    ['value'=>'en_tramite','label'=>'EN TRÁMITE'],
                    ['value'=>'en_juicio','label'=>'EN JUICIO'],
                    ['value'=>'concluido','label'=>'CONCLUIDO'],
                    ['value'=>'cancelado','label'=>'CANCELADO'],
                ],
            ],
            'etapas' => $juicio->etapas()
                ->with(['etiqueta:id,nombre','usuario:id,name'])
                ->orderByDesc('fecha_vencimiento')
                ->get(),
            'fecha_inicio_juicio' => optional($juicio->fecha_inicio)->format('Y-m-d'),
        ]);
    }

   public function store(Request $request, Juicio $juicio)
{
    $data = $request->validate([
        'etiqueta_id'       => ['nullable','exists:etiquetas,id'],
        'etapa'             => ['required','string','max:255'],
        'usuario_id'        => ['nullable','exists:users,id'],
        'rol'               => ['nullable','string','max:100'],
        'comentarios'       => ['nullable','string'],

        // ✅ nueva validación
        'fecha_inicio'      => ['nullable','date'],

        'dias_vencimiento'  => ['nullable','integer','min:0'],
        'fecha_vencimiento' => ['nullable','date'],
        'estatus'           => ['required','in:en_tramite,en_juicio,concluido,cancelado'],
        'archivo'           => ['nullable','file','max:10240'],
    ]);

    // Si no mandan fecha_vencimiento, la calculamos:
    if (empty($data['fecha_vencimiento'])) {
        $dias = (int)($data['dias_vencimiento'] ?? 0);
        $base = $data['fecha_inicio']
            ? Carbon::parse($data['fecha_inicio'])
            : ($juicio->fecha_inicio ? Carbon::parse($juicio->fecha_inicio) : now());

        if ($dias > 0) {
            $data['fecha_vencimiento'] = $base->copy()->addDays($dias)->format('Y-m-d');
        }
    }

    if ($request->hasFile('archivo')) {
        $data['archivo_path'] = $request->file('archivo')->store('etapas', 'public');
    }

    $data['juicio_id'] = $juicio->id;

    Etapa::create($data);

    return back()->with('success', 'Etapa registrada.');
}
}
