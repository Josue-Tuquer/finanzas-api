<?php

namespace App\Http\Requests\Egreso;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class EgresoRequest extends FormRequest
{
    protected function rulesFor(string $required): array
    {
        return [
            'categoria_id' => [$required, 'integer'],
            'subcategoria_id' => ['nullable', 'integer'],
            'fecha' => [$required, 'date'],
            'descripcion' => [$required, 'string', 'max:150'],
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

            $subcategoriaId = $this->input('subcategoria_id');

            if ($subcategoriaId !== null && ! $this->subcategoriaValida((int) $subcategoriaId, $categoriaId)) {
                $validator->errors()->add('subcategoria_id', 'La subcategoría seleccionada no es válida.');
            }
        }];
    }

    private function categoriaPermitida(int $categoriaId): bool
    {
        return Categoria::query()
            ->whereKey($categoriaId)
            ->where('tipo', 'egreso')
            ->where(function ($query): void {
                $query->where('user_id', $this->user()->id)
                    ->orWhereNull('user_id');
            })
            ->exists();
    }

    private function subcategoriaValida(int $subcategoriaId, mixed $categoriaId): bool
    {
        if ($categoriaId === null) {
            return false;
        }

        return Subcategoria::query()
            ->whereKey($subcategoriaId)
            ->where('categoria_id', (int) $categoriaId)
            ->whereHas('categoria', function ($query): void {
                $query->where('tipo', 'egreso')
                    ->where(function ($query): void {
                        $query->where('user_id', $this->user()->id)
                            ->orWhereNull('user_id');
                    });
            })
            ->exists();
    }
}
