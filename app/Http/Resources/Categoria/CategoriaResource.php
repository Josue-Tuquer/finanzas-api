<?php

namespace App\Http\Resources\Categoria;

use App\Http\Resources\Subcategoria\SubcategoriaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'es_personal' => $this->user_id !== null,
            'subcategorias' => SubcategoriaResource::collection(
                $this->whenLoaded('subcategorias')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
