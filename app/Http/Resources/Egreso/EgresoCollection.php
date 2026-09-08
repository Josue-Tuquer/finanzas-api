<?php

namespace App\Http\Resources\Egreso;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EgresoCollection extends ResourceCollection
{
    public $collects = EgresoResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }       
    
}
