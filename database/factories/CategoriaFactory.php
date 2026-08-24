<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Categoria>
     */
    protected $model = Categoria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nombre' => fake()->unique()->bothify('Categoría ###??'),
            'tipo' => fake()->randomElement(['ingreso', 'egreso']),
        ];
    }
}
