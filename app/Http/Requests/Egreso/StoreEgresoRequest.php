<?php

namespace App\Http\Requests\Egreso;

class StoreEgresoRequest extends EgresoRequest
{
    public function rules(): array
    {
        return $this->rulesFor('required');
    }
}
