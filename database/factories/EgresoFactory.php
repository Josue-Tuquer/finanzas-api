<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Egreso>
 */
class EgresoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Egreso>
     */
    protected $model = Egreso::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->state(['tipo' => 'egreso']),
            'subcategoria_id' => null,
            'fecha' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'descripcion' => fake()->sentence(3),
            'monto' => number_format(fake()->numberBetween(25, 650), 2, '.', ''),
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
