<?php

namespace App\Http\Requests\Egreso;

use Illuminate\Foundation\Http\FormRequest;

class IndexEgresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anio' => ['nullable', 'integer', 'between:2000,2100'],
            'mes' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
