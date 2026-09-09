<?php

namespace App\Http\Resources\Subcategoria;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubcategoriaCollection extends ResourceCollection
{
    public $collects = SubcategoriaResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->all();
    }
}
