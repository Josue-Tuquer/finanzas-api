<?php

namespace App\Http\Requests\Subcategoria;

class StoreSubcategoriaRequest extends SubcategoriaRequest
{
    public function rules(): array
    {
        return $this->rulesFor('required');
    }
}
