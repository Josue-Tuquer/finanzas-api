<?php

namespace App\Http\Requests\Egreso;

class UpdateEgresoRequest extends EgresoRequest
{
    public function rules(): array
    {
        return $this->rulesFor('sometimes');
    }
}
