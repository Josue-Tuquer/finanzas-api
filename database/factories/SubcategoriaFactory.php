<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subcategoria>
 */
class SubcategoriaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Subcategoria>
     */
    protected $model = Subcategoria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory()->state(['tipo' => 'egreso']),
            'nombre' => fake()->unique()->words(2, true),
        ];
    }
}
