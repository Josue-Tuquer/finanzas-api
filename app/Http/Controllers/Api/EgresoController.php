<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Egreso\IndexEgresoRequest;
use App\Http\Requests\Egreso\StoreEgresoRequest;
use App\Http\Requests\Egreso\UpdateEgresoRequest;
use App\Http\Resources\Egreso\EgresoCollection;
use App\Http\Resources\Egreso\EgresoResource;
use App\Models\Egreso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EgresoController extends Controller
{
    public function index(IndexEgresoRequest $request): EgresoCollection
    {
        $filters = $request->validated();

        $egresos = Egreso::query()
            ->where('user_id', $request->user()->id)
            ->with(['categoria', 'subcategoria'])
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

        return new EgresoCollection($egresos);
    }

    public function store(StoreEgresoRequest $request): JsonResponse
    {
        $egreso = Egreso::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $egreso->load(['categoria', 'subcategoria']);

        return (new EgresoResource($egreso))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $egreso): EgresoResource
    {
        return new EgresoResource($this->egresoDelUsuario($request, $egreso));
    }

    public function update(UpdateEgresoRequest $request, int $egreso): EgresoResource
    {
        $egreso = $this->egresoDelUsuario($request, $egreso);

        $egreso->update($request->validated());
        $egreso->load(['categoria', 'subcategoria']);

        return new EgresoResource($egreso);
    }

    public function destroy(Request $request, int $egreso): Response
    {
        $this->egresoDelUsuario($request, $egreso)->delete();

        return response()->noContent();
    }

    private function egresoDelUsuario(Request $request, int $egreso): Egreso
    {
        return Egreso::query()
            ->where('user_id', $request->user()->id)
            ->with(['categoria', 'subcategoria'])
            ->findOrFail($egreso);
    }
}
