<?php

namespace App\Http\Requests\Subcategoria;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class SubcategoriaRequest extends FormRequest
{
    protected function rulesFor(string $required): array
    {
        return [
            'nombre' => [$required, 'string', 'max:80'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $categoria = Categoria::query()
                ->where('user_id', $this->user()->id)
                ->find($this->route('categoria'));

            if (! $categoria) {
                return;
            }

            $subcategoriaActual = $this->subcategoriaActual($categoria->id);
            $nombre = $this->input('nombre', $subcategoriaActual?->nombre);

            if ($nombre === null) {
                return;
            }

            $query = Subcategoria::query()
                ->where('categoria_id', $categoria->id)
                ->where('nombre', $nombre);

            if ($subcategoriaActual) {
                $query->whereKeyNot($subcategoriaActual->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('nombre', 'Ya existe una subcategoría con ese nombre.');
            }
        }];
    }

    private function subcategoriaActual(int $categoriaId): ?Subcategoria
    {
        $subcategoriaId = $this->route('subcategoria');

        if (! $subcategoriaId) {
            return null;
        }

        return Subcategoria::query()
            ->where('categoria_id', $categoriaId)
            ->find($subcategoriaId);
    }
}
