<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Juicio;
use App\Models\Etapa;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Métricas básicas
        $totalJuicios     = Juicio::count();
        $juiciosEnProceso = Juicio::where('estatus', 'en_proceso')->count();
        $juiciosAutoriz   = Juicio::where('estatus', 'autorizado')->count();
        $juiciosConcl     = Juicio::where('estatus', 'concluido')->count();

        // Próximos vencimientos de etapas (próximos 15 días)
        $hoy = Carbon::today();
        $proximosVenc = Etapa::with(['juicio:id,nombre', 'etiqueta:id,nombre'])
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(15)])
            ->orderBy('fecha_vencimiento')
            ->limit(6)
            ->get(['id','juicio_id','etiqueta_id','etapa','fecha_vencimiento','estatus']);

        // Últimos juicios creados
        $ultimosJuicios = Juicio::with('cliente:id,nombre')
            ->latest('created_at')
            ->limit(6)
            ->get(['id','nombre','cliente_id','estatus','created_at','fecha_inicio','monto']);

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total'        => $totalJuicios,
                'proceso'      => $juiciosEnProceso,
                'autorizados'  => $juiciosAutoriz,
                'concluidos'   => $juiciosConcl,
            ],
            'proximosVencimientos' => $proximosVenc,
            'ultimosJuicios'       => $ultimosJuicios,
        ]);
    }
}
