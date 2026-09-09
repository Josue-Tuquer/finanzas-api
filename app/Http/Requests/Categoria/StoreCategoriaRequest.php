<?php

namespace App\Http\Requests\Categoria;

class StoreCategoriaRequest extends CategoriaRequest
{
    public function rules(): array
    {
        return $this->rulesFor('required');
    }
}
