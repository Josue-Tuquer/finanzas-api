<?php

namespace App\Http\Resources\Ingreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IngresoCollection extends ResourceCollection
{
    public $collects = IngresoResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->all();
    }
}
