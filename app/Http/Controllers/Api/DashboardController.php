<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ResumenDashboardRequest;
use App\Models\Egreso;
use App\Models\Ingreso;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function resumen(ResumenDashboardRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $inicioAnio = CarbonImmutable::create($filters['anio'], 1, 1)->startOfDay();
        $inicioMes = CarbonImmutable::create($filters['anio'], $filters['mes'], 1)->startOfDay();
        $inicioMesSiguiente = $inicioMes->addMonth();

        $ingresos = Ingreso::query()
            ->where('user_id', $request->user()->id)
            ->where('fecha', '>=', $inicioAnio->toDateString())
            ->where('fecha', '<', $inicioMesSiguiente->toDateString())
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN fecha >= ? THEN monto ELSE 0 END), 0) as ingresos_mes, COALESCE(SUM(monto), 0) as ingresos_acumulados',
                [$inicioMes->toDateString()]
            );

        $egresos = Egreso::query()
            ->where('user_id', $request->user()->id)
            ->where('fecha', '>=', $inicioAnio->toDateString())
            ->where('fecha', '<', $inicioMesSiguiente->toDateString())
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN fecha >= ? THEN monto ELSE 0 END), 0) as egresos_mes, COALESCE(SUM(monto), 0) as egresos_acumulados',
                [$inicioMes->toDateString()]
            );

        $resumen = DB::query()
            ->fromSub($ingresos, 'ingresos')
            ->crossJoinSub($egresos, 'egresos')
            ->selectRaw(
                'ingresos_mes, egresos_mes, ingresos_mes - egresos_mes as balance_mes, ingresos_acumulados, egresos_acumulados, ingresos_acumulados - egresos_acumulados as balance_acumulado, COALESCE(ROUND((egresos_mes * 100.00) / NULLIF(ingresos_mes, 0), 2), 0) as porcentaje_gastado'
            )
            ->first();

        return response()->json([
            'ingresos_mes' => (string) $resumen->ingresos_mes,
            'egresos_mes' => (string) $resumen->egresos_mes,
            'balance_mes' => (string) $resumen->balance_mes,
            'ingresos_acumulados' => (string) $resumen->ingresos_acumulados,
            'egresos_acumulados' => (string) $resumen->egresos_acumulados,
            'balance_acumulado' => (string) $resumen->balance_acumulado,
            'porcentaje_gastado' => (string) $resumen->porcentaje_gastado,
        ]);
    }
}
