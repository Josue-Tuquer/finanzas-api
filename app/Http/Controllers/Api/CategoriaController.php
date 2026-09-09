<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categoria\IndexCategoriaRequest;
use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use App\Http\Resources\Categoria\CategoriaCollection;
use App\Http\Resources\Categoria\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriaController extends Controller
{
    public function index(IndexCategoriaRequest $request): CategoriaCollection
    {
        $filters = $request->validated();

        $categorias = $this->categoriasDisponibles($request)
            ->with('subcategorias')
            ->when(
                isset($filters['tipo']),
                fn ($query) => $query->where('tipo', $filters['tipo'])
            )
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return new CategoriaCollection($categorias);
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $categoria->load('subcategorias');

        return (new CategoriaResource($categoria))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $categoria): CategoriaResource
    {
        return new CategoriaResource($this->categoriaDisponible($request, $categoria));
    }

    public function update(UpdateCategoriaRequest $request, int $categoria): CategoriaResource
    {
        $categoria = $this->categoriaDelUsuario($request, $categoria);

        $categoria->update($request->validated());
        $categoria->load('subcategorias');

        return new CategoriaResource($categoria);
    }

    public function destroy(Request $request, int $categoria): JsonResponse|Response
    {
        $categoria = $this->categoriaDelUsuario($request, $categoria);

        if ($categoria->ingresos()->exists() || $categoria->egresos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con movimientos asociados.',
            ], 409);
        }

        $categoria->delete();

        return response()->noContent();
    }

    private function categoriasDisponibles(Request $request)
    {
        return Categoria::query()
            ->where(function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id)
                    ->orWhereNull('user_id');
            });
    }

    private function categoriaDisponible(Request $request, int $categoria): Categoria
    {
        return $this->categoriasDisponibles($request)
            ->with('subcategorias')
            ->findOrFail($categoria);
    }

    private function categoriaDelUsuario(Request $request, int $categoria): Categoria
    {
        return Categoria::query()
            ->where('user_id', $request->user()->id)
            ->with('subcategorias')
            ->findOrFail($categoria);
    }
}
