<?php

namespace App\Http\Resources\Ingreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngresoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoria_id' => $this->categoria_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'fuente' => $this->fuente,
            'monto' => (string) $this->monto,
            'notas' => $this->notas,
            'categoria' => $this->whenLoaded(
                'categoria',
                fn () => new CategoriaIngresoResource($this->categoria)
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
