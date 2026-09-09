<?php

namespace App\Http\Requests\Ingreso;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class IngresoRequest extends FormRequest
{
    protected function rulesFor(string $required): array
    {
        return [
            'categoria_id' => [$required, 'integer'],
            'fecha' => [$required, 'date'],
            'fuente' => [$required, 'string', 'max:150'],
            'monto' => [$required, 'decimal:0,2', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/', 'gt:0'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $categoriaId = $this->input('categoria_id');

            if ($categoriaId !== null && ! $this->categoriaPermitida((int) $categoriaId)) {
                $validator->errors()->add('categoria_id', 'La categoría seleccionada no es válida.');
            }
        }];
    }

    private function categoriaPermitida(int $categoriaId): bool
    {
        return Categoria::query()
            ->whereKey($categoriaId)
            ->where('tipo', 'ingreso')
            ->where(function ($query): void {
                $query->where('user_id', $this->user()->id)
                    ->orWhereNull('user_id');
            })
            ->exists();
    }
}
