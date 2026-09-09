<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subcategoria\StoreSubcategoriaRequest;
use App\Http\Requests\Subcategoria\UpdateSubcategoriaRequest;
use App\Http\Resources\Subcategoria\SubcategoriaCollection;
use App\Http\Resources\Subcategoria\SubcategoriaResource;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubcategoriaController extends Controller
{
    public function index(Request $request, int $categoria): SubcategoriaCollection
    {
        $categoria = $this->categoriaDisponible($request, $categoria);

        return new SubcategoriaCollection(
            $categoria->subcategorias()->orderBy('nombre')->get()
        );
    }

    public function store(StoreSubcategoriaRequest $request, int $categoria): JsonResponse
    {
        $categoria = $this->categoriaDelUsuario($request, $categoria);

        $subcategoria = $categoria->subcategorias()->create($request->validated());

        return (new SubcategoriaResource($subcategoria))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $categoria, int $subcategoria): SubcategoriaResource
    {
        $this->categoriaDisponible($request, $categoria);

        return new SubcategoriaResource(
            $this->subcategoriaDeCategoria($request, $categoria, $subcategoria)
        );
    }

    public function update(
        UpdateSubcategoriaRequest $request,
        int $categoria,
        int $subcategoria
    ): SubcategoriaResource {
        $this->categoriaDelUsuario($request, $categoria);

        $subcategoria = $this->subcategoriaDeCategoria($request, $categoria, $subcategoria);
        $subcategoria->update($request->validated());

        return new SubcategoriaResource($subcategoria);
    }

    public function destroy(Request $request, int $categoria, int $subcategoria): Response
    {
        $this->categoriaDelUsuario($request, $categoria);

        $this->subcategoriaDeCategoria($request, $categoria, $subcategoria)->delete();

        return response()->noContent();
    }

    private function categoriaDisponible(Request $request, int $categoria): Categoria
    {
        return Categoria::query()
            ->where(function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id)
                    ->orWhereNull('user_id');
            })
            ->findOrFail($categoria);
    }

    private function categoriaDelUsuario(Request $request, int $categoria): Categoria
    {
        return Categoria::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($categoria);
    }

    private function subcategoriaDeCategoria(
        Request $request,
        int $categoria,
        int $subcategoria
    ): Subcategoria {
        return Subcategoria::query()
            ->where('categoria_id', $categoria)
            ->whereHas('categoria', function ($query) use ($request): void {
                $query->where(function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id)
                        ->orWhereNull('user_id');
                });
            })
            ->findOrFail($subcategoria);
    }
}
