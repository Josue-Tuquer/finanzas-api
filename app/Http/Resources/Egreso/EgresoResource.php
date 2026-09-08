<?php

namespace App\Http\Resources\Egreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EgresoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoria_id' => $this->categoria_id,
            'subcategoria_id' => $this->subcategoria_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'descripcion' => $this->descripcion,
            'monto' => (string) $this->monto,
            'notas' => $this->notas,
            'categoria' => $this->whenLoaded(
                'categoria',
                fn () => new CategoriaEgresoResource($this->categoria)
            ),
            'subcategoria' => $this->whenLoaded(
                'subcategoria',
                fn () => $this->subcategoria
                    ? new SubcategoriaEgresoResource($this->subcategoria)
                    : null
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
