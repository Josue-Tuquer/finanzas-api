<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ResumenAnualDashboardRequest;
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

    public function egresosPorCategoria(ResumenDashboardRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $inicioMes = CarbonImmutable::create($filters['anio'], $filters['mes'], 1)->startOfDay();
        $inicioMesSiguiente = $inicioMes->addMonth();

        $egresosPorCategoria = Egreso::query()
            ->join('categorias', 'categorias.id', '=', 'egresos.categoria_id')
            ->where('egresos.user_id', $request->user()->id)
            ->where('egresos.fecha', '>=', $inicioMes->toDateString())
            ->where('egresos.fecha', '<', $inicioMesSiguiente->toDateString())
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('total')
            ->selectRaw('categorias.id, categorias.nombre, SUM(egresos.monto) as total')
            ->get()
            ->map(fn ($categoria) => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'total' => (string) $categoria->total,
            ]);

        return response()->json($egresosPorCategoria);
    }

    public function resumenAnual(ResumenAnualDashboardRequest $request): JsonResponse
    {
        $anio = $request->validated('anio');
        $inicioAnio = CarbonImmutable::create($anio, 1, 1)->startOfDay();
        $inicioSiguienteAnio = $inicioAnio->addYear();

        $ingresos = Ingreso::query()
            ->where('user_id', $request->user()->id)
            ->where('fecha', '>=', $inicioAnio->toDateString())
            ->where('fecha', '<', $inicioSiguienteAnio->toDateString())
            ->selectRaw('CAST(SUBSTR(fecha, 6, 2) AS UNSIGNED) as mes, SUM(monto) as ingresos, 0 as egresos')
            ->groupBy('mes');

        $egresos = Egreso::query()
            ->where('user_id', $request->user()->id)
            ->where('fecha', '>=', $inicioAnio->toDateString())
            ->where('fecha', '<', $inicioSiguienteAnio->toDateString())
            ->selectRaw('CAST(SUBSTR(fecha, 6, 2) AS UNSIGNED) as mes, 0 as ingresos, SUM(monto) as egresos')
            ->groupBy('mes');

        $totalesPorMes = DB::query()
            ->fromSub($ingresos->unionAll($egresos), 'movimientos')
            ->selectRaw('mes, SUM(ingresos) as ingresos, SUM(egresos) as egresos, SUM(ingresos) - SUM(egresos) as balance')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $resumenAnual = collect(range(1, 12))->map(function (int $mes) use ($totalesPorMes): array {
            $totales = $totalesPorMes->get($mes);

            return [
                'mes' => $mes,
                'ingresos' => (string) ($totales->ingresos ?? 0),
                'egresos' => (string) ($totales->egresos ?? 0),
                'balance' => (string) ($totales->balance ?? 0),
            ];
        });

        return response()->json($resumenAnual);
    }
}
