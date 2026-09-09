<?php

namespace App\Http\Requests\Categoria;

use App\Models\Categoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class CategoriaRequest extends FormRequest
{
    protected function rulesFor(string $required): array
    {
        return [
            'nombre' => [$required, 'string', 'max:80'],
            'tipo' => [$required, Rule::in(['ingreso', 'egreso'])],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $categoriaActual = $this->categoriaActual();
            $nombre = $this->input('nombre', $categoriaActual?->nombre);
            $tipo = $this->input('tipo', $categoriaActual?->tipo);

            if ($nombre === null || $tipo === null) {
                return;
            }

            $query = Categoria::query()
                ->where('user_id', $this->user()->id)
                ->where('nombre', $nombre)
                ->where('tipo', $tipo);

            if ($categoriaActual) {
                $query->whereKeyNot($categoriaActual->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('nombre', 'Ya existe una categoría propia con ese nombre y tipo.');
            }
        }];
    }

    private function categoriaActual(): ?Categoria
    {
        $categoriaId = $this->route('categoria');

        if (! $categoriaId) {
            return null;
        }

        return Categoria::query()
            ->where('user_id', $this->user()->id)
            ->find($categoriaId);
    }
}
