<?php

namespace App\Http\Requests\Ingreso;

class UpdateIngresoRequest extends IngresoRequest
{
    public function rules(): array
    {
        return $this->rulesFor('sometimes');
    }
}
