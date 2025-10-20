<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class BusinessDays
{
    /**
     * @return Collection<string>  // "Y-m-d"
     */
    public static function holidaysForAuthority(int $autoridadId, int $year): Collection
    {
        // Ajusta al nombre real de tu tabla de festivos
        return \DB::table('dias_festivos')
            ->where('autoridad_id', $autoridadId)
            ->whereYear('fecha', $year)
            ->pluck('fecha')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
    }

    public static function isBusinessDay(Carbon $date, Collection $holidays): bool
    {
        if ($date->isWeekend()) return false;
        return !$holidays->contains($date->toDateString());
    }

    /** Suma N días hábiles (útil si quieres recalcular vencimientos). */
    public static function addBusinessDays(Carbon $date, int $days, Collection $holidays): Carbon
    {
        $d = $date->copy();
        $added = 0;
        while ($added < $days) {
            $d->addDay();
            if (self::isBusinessDay($d, $holidays)) $added++;
        }
        return $d;
    }

    /** Diferencia en días hábiles entre dos fechas (excluye hoy si hoy no es hábil). */
    public static function diffInBusinessDays(Carbon $from, Carbon $to, Collection $holidays): int
    {
        $from = $from->copy()->startOfDay();
        $to   = $to->copy()->startOfDay();

        if ($to->lessThan($from)) return 0;

        $days = 0;
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (self::isBusinessDay($d, $holidays)) $days++;
        }

        // si quieres que "hoy" cuente como 0 restante, en lugar de 1, quita 1 si hoy es hábil
        if (self::isBusinessDay($from, $holidays)) $days--;

        return max(0, $days);
    }
}
