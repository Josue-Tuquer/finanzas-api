<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ingreso\IndexIngresoRequest;
use App\Http\Requests\Ingreso\StoreIngresoRequest;
use App\Http\Requests\Ingreso\UpdateIngresoRequest;
use App\Http\Resources\Ingreso\IngresoCollection;
use App\Http\Resources\Ingreso\IngresoResource;
use App\Models\Ingreso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IngresoController extends Controller
{
    public function index(IndexIngresoRequest $request): IngresoCollection
    {
        $filters = $request->validated();

        $ingresos = Ingreso::query()
            ->where('user_id', $request->user()->id)
            ->with('categoria')
            ->when(
                isset($filters['anio']),
                fn ($query) => $query->whereYear('fecha', $filters['anio'])
            )
            ->when(
                isset($filters['mes']),
                fn ($query) => $query->whereMonth('fecha', $filters['mes'])
            )
            ->orderByDesc('fecha')
            ->get();

        return new IngresoCollection($ingresos);
    }

    public function store(StoreIngresoRequest $request): JsonResponse
    {
        $ingreso = Ingreso::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $ingreso->load('categoria');

        return (new IngresoResource($ingreso))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $ingreso): IngresoResource
    {
        return new IngresoResource($this->ingresoDelUsuario($request, $ingreso));
    }

    public function update(UpdateIngresoRequest $request, int $ingreso): IngresoResource
    {
        $ingreso = $this->ingresoDelUsuario($request, $ingreso);

        $ingreso->update($request->validated());
        $ingreso->load('categoria');

        return new IngresoResource($ingreso);
    }

    public function destroy(Request $request, int $ingreso): Response
    {
        $this->ingresoDelUsuario($request, $ingreso)->delete();

        return response()->noContent();
    }

    private function ingresoDelUsuario(Request $request, int $ingreso): Ingreso
    {
        return Ingreso::query()
            ->where('user_id', $request->user()->id)
            ->with('categoria')
            ->findOrFail($ingreso);
    }
}
