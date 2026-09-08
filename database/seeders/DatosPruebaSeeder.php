<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\Subcategoria;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategoriasSeeder::class);

        $usuarios = [
            [
                'name' => 'Ana López',
                'email' => 'ana.lopez@finanzas.test',
                'ingresos' => [1 => 3_500, 2 => 3_500, 3 => 3_700, 4 => 3_600, 5 => 3_800, 7 => 3_750],
                'ajuste_egresos' => 0,
            ],
            [
                'name' => 'Carlos Méndez',
                'email' => 'carlos.mendez@finanzas.test',
                'ingresos' => [1 => 4_200, 2 => 4_100, 3 => 4_300, 4 => 4_250, 5 => 4_400, 7 => 4_350],
                'ajuste_egresos' => 40,
            ],
        ];

        $categorias = Categoria::query()
            ->whereNull('user_id')
            ->pluck('id', 'nombre');

        $gastos = [
            ['categoria' => 'Vivienda', 'subcategoria' => 'Alquiler', 'descripcion' => 'Alquiler de habitación', 'monto' => 1_450],
            ['categoria' => 'Alimentación', 'subcategoria' => 'Supermercado', 'descripcion' => 'Compras de supermercado', 'monto' => 650],
            ['categoria' => 'Transporte', 'subcategoria' => 'Bus', 'descripcion' => 'Transporte urbano', 'monto' => 240],
            ['categoria' => 'Educación', 'subcategoria' => 'Cursos', 'descripcion' => 'Materiales y cursos', 'monto' => 200],
            ['categoria' => 'Salud', 'subcategoria' => 'Medicamentos', 'descripcion' => 'Medicamentos básicos', 'monto' => 100],
            ['categoria' => 'Ocio / Entretenimiento', 'subcategoria' => 'Suscripciones', 'descripcion' => 'Suscripciones digitales', 'monto' => 90],
            ['categoria' => 'Deporte', 'subcategoria' => 'Gimnasio', 'descripcion' => 'Mensualidad de gimnasio', 'monto' => 180],
            ['categoria' => 'Imprevistos', 'subcategoria' => 'Reparaciones', 'descripcion' => 'Reparación menor', 'monto' => 120],
            ['categoria' => 'Otro Egreso', 'subcategoria' => null, 'descripcion' => 'Gastos varios', 'monto' => 150],
        ];

        foreach ($usuarios as $datosUsuario) {
            $usuario = User::query()->updateOrCreate(
                ['email' => $datosUsuario['email']],
                [
                    'name' => $datosUsuario['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
                
            );

            foreach ($datosUsuario['ingresos'] as $mes => $monto) {
                $fecha = sprintf('2026-%02d-05', $mes);

                Ingreso::query()->updateOrCreate(
                    [
                        'user_id' => $usuario->id,
                        'fecha' => $fecha,
                        'fuente' => 'Trabajo de medio tiempo y apoyo familiar',
                    ],
                    [
                        'categoria_id' => $categorias['Empleo'],
                        'monto' => number_format($monto, 2, '.', ''),
                        'notas' => 'Ingreso mensual de prueba.',
                    ],
                );
            }

            foreach (array_keys($datosUsuario['ingresos']) as $mes) {
                $gastosDelMes = array_values(array_filter(
                    $gastos,
                    fn (array $gasto, int $indice): bool => $indice !== $mes % count($gastos),
                    ARRAY_FILTER_USE_BOTH,
                ));

                foreach ($gastosDelMes as $indice => $gasto) {
                    $categoriaId = $categorias[$gasto['categoria']];
                    $subcategoriaId = $gasto['subcategoria'] === null
                        ? null
                        : Subcategoria::query()
                            ->where('categoria_id', $categoriaId)
                            ->where('nombre', $gasto['subcategoria'])
                            ->value('id');
                    $fecha = sprintf('2026-%02d-%02d', $mes, 2 + ($indice * 2));

                    Egreso::query()->updateOrCreate(
                        [
                            'user_id' => $usuario->id,
                            'fecha' => $fecha,
                            'descripcion' => $gasto['descripcion'],
                        ],
                        [
                            'categoria_id' => $categoriaId,
                            'subcategoria_id' => $subcategoriaId,
                            'monto' => number_format(
                                $gasto['monto'] + $datosUsuario['ajuste_egresos'],
                                2,
                                '.',
                                '',
                            ),
                            'notas' => 'Egreso mensual de prueba.',
                        ],
                    );
                }
            }
        }
    }
}
