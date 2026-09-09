<?php

namespace App\Http\Requests\Categoria;

class UpdateCategoriaRequest extends CategoriaRequest
{
    public function rules(): array
    {
        return $this->rulesFor('sometimes');
    }
}
