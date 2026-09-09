<?php

namespace App\Http\Requests\Ingreso;

class StoreIngresoRequest extends IngresoRequest
{
    public function rules(): array
    {
        return $this->rulesFor('required');
    }
}
