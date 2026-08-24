<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingreso>
 */
class IngresoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Ingreso>
     */
    protected $model = Ingreso::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->state(['tipo' => 'ingreso']),
            'fecha' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'fuente' => fake()->randomElement(['Trabajo de medio tiempo', 'Apoyo familiar', 'Freelance']),
            'monto' => number_format(fake()->numberBetween(1_500, 6_000), 2, '.', ''),
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
