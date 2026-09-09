<?php

namespace App\Http\Resources\Categoria;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoriaCollection extends ResourceCollection
{
    public $collects = CategoriaResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->all();
    }
}
