<?php
// app/Http/Controllers/CalendarioController.php
namespace App\Http\Controllers;

use App\Models\DiaFestivo;
use App\Models\Autoridad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $autoridades = Autoridad::select('id','nombre')->orderBy('nombre')->get();

        $autoridadId = (int) $request->query('autoridad_id', $autoridades->first()->id ?? 0);
        $anio        = (int) $request->query('anio', (int) now()->format('Y'));

        $dias = DiaFestivo::query()
            ->where('autoridad_id', $autoridadId ?: -1)
            ->where('anio', $anio)
            ->orderBy('fecha')
            ->get()
            ->map(fn($d)=>[
                'id' => $d->id,
                'fecha' => $d->fecha->format('Y-m-d'),
                'dia_semana' => $d->fecha->locale('es')->dayName, // “lunes”, “martes”…
                'descripcion' => $d->descripcion,
            ]);

        return Inertia::render('Calendario/Index', [
            'autoridades' => $autoridades,
            'filtros' => ['autoridad_id' => $autoridadId, 'anio' => $anio],
            'dias' => $dias,
        ]);
    }

public function upload(Request $request)
{
    $data = $request->validate([
        'autoridad_id' => ['required','exists:autoridades,id'],
        'anio'         => ['required','integer','min:1900','max:2100'],
        'archivo'      => ['required','file','mimes:csv,txt','max:5120'],
        'reemplazar'   => ['nullable','boolean'],
    ]);

    $autoridadId = (int) $data['autoridad_id'];
    $anio        = (int) $data['anio'];
    $reemplazar  = (bool) ($data['reemplazar'] ?? false);

    // 1) Leer todo el archivo
    $raw = file_get_contents($request->file('archivo')->getRealPath());
    if ($raw === false) {
        return back()->with('error','No se pudo leer el archivo.');
    }

    // 2) Quitar BOM y normalizar fin de línea a \n
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // BOM UTF-8
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);

    // 3) Asegurar UTF-8 (Excel a veces guarda Windows-1252)
    if (!mb_detect_encoding($raw, 'UTF-8', true)) {
        $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252,ISO-8859-1,ASCII');
    }

    $lines = array_values(array_filter(explode("\n", $raw), fn($l) => trim($l) !== ''));

    if (empty($lines)) {
        return back()->with('error','El CSV está vacío.');
    }

    // 4) Detectar delimitador usando la primera línea
    $first = $lines[0];
    $delimiter = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';

    $rows = [];
    $counters = [
        'procesadas' => 0,
        'saltadas_vacias' => 0,
        'saltadas_header' => 0,
        'saltadas_fecha'  => 0,
        'saltadas_anio'   => 0,
    ];

    foreach ($lines as $i => $line) {
        // parsear la línea como CSV con el delimitador detectado
        $cols = str_getcsv($line, $delimiter);

        // línea vacía
        if (!isset($cols[0]) || trim($cols[0]) === '') {
            $counters['saltadas_vacias']++; continue;
        }

        // header (“fecha” en la primera columna)
        $c0 = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$cols[0]));
        if (mb_strtolower($c0) === 'fecha') {
            $counters['saltadas_header']++; continue;
        }

        $fechaRaw = $c0;
        $desc     = isset($cols[1]) ? trim((string)$cols[1]) : '';

        // 5) Convertir dd/mm/yyyy o d/m/Y
        try {
            $fecha = Carbon::createFromFormat('d/m/Y', $fechaRaw);
        } catch (\Throwable $e) {
            try {
                $fecha = Carbon::createFromFormat('d-m-Y', $fechaRaw);
            } catch (\Throwable $e2) {
                $counters['saltadas_fecha']++;
                Log::warning("CSV: fecha inválida en línea ".($i+1).": '{$fechaRaw}'");
                continue;
            }
        }

        // 6) Validar año
        if ((int)$fecha->year !== $anio) {
            $counters['saltadas_anio']++;
            continue;
        }

        $rows[] = [
            'autoridad_id' => $autoridadId,
            'anio'         => $anio,
            'fecha'        => $fecha->format('Y-m-d'),
            'descripcion'  => $desc !== '' ? $desc : null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
        $counters['procesadas']++;
    }

    if (empty($rows)) {
        // deja rastro claro del porqué
        Log::info('CSV sin filas válidas', $counters + ['muestra_primera_linea' => $first, 'delimiter' => $delimiter]);
        return back()->with('error',
            "No se encontraron filas válidas. ".
            "Revisa: formato de fecha (dd/mm/yyyy), delimitador ".($delimiter===';'?';':',').", y que el año sea {$anio}. ".
            "Detalle: ".json_encode($counters));
    }

    DB::transaction(function () use ($rows, $reemplazar, $autoridadId, $anio) {
        if ($reemplazar) {
            DiaFestivo::where('autoridad_id',$autoridadId)->where('anio',$anio)->delete();
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DiaFestivo::upsert($chunk, ['autoridad_id','anio','fecha'], ['descripcion','updated_at']);
        }
    });

    return redirect()
        ->route('calendario.index', ['autoridad_id'=>$autoridadId, 'anio'=>$anio])
        ->with('success', "Cargadas {$counters['procesadas']} filas. ".
                          "Saltadas: vacías {$counters['saltadas_vacias']}, encabezados {$counters['saltadas_header']}, ".
                          "fecha inválida {$counters['saltadas_fecha']}, año distinto {$counters['saltadas_anio']}.");
}

}
