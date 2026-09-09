<?php

namespace App\Http\Requests\Subcategoria;

class UpdateSubcategoriaRequest extends SubcategoriaRequest
{
    public function rules(): array
    {
        return $this->rulesFor('sometimes');
    }
}
