<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class ResumenDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anio' => ['required', 'integer', 'between:2000,2100'],
            'mes' => ['required', 'integer', 'between:1,12'],
        ];
    }
}
