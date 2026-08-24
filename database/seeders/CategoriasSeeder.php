<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categorias = [
            'ingreso' => [
                ['nombre' => 'Empleo', 'subcategorias' => []],
                ['nombre' => 'Freelance / Proyecto', 'subcategorias' => []],
                ['nombre' => 'Negocio Propio', 'subcategorias' => []],
                ['nombre' => 'Inversión / Dividendos', 'subcategorias' => []],
                ['nombre' => 'Bono / Extra', 'subcategorias' => []],
                ['nombre' => 'Otro Ingreso', 'subcategorias' => []],
            ],
            'egreso' => [
                ['nombre' => 'Vivienda', 'subcategorias' => ['Alquiler', 'Agua', 'Luz', 'Internet']],
                ['nombre' => 'Educación', 'subcategorias' => ['Universidad', 'Cursos', 'Libros']],
                ['nombre' => 'Alimentación', 'subcategorias' => ['Supermercado', 'Restaurante', 'Almuerzo']],
                ['nombre' => 'Transporte', 'subcategorias' => ['Gasolina', 'Bus', 'Taxi / Uber', 'Parqueo']],
                ['nombre' => 'Salud', 'subcategorias' => ['Consulta', 'Medicamentos', 'Laboratorio']],
                ['nombre' => 'Ocio / Entretenimiento', 'subcategorias' => ['Suscripciones', 'Cine', 'Salidas']],
                ['nombre' => 'Deporte', 'subcategorias' => ['Gimnasio', 'Equipo deportivo']],
                ['nombre' => 'Imprevistos', 'subcategorias' => ['Emergencias', 'Reparaciones']],
                ['nombre' => 'Otro Egreso', 'subcategorias' => []],
            ],
        ];

        foreach ($categorias as $tipo => $items) {
            foreach ($items as $item) {
                $categoria = Categoria::query()->firstOrCreate([
                    'user_id' => null,
                    'nombre' => $item['nombre'],
                    'tipo' => $tipo,
                ]);

                foreach ($item['subcategorias'] as $nombreSubcategoria) {
                    Subcategoria::query()->firstOrCreate([
                        'categoria_id' => $categoria->id,
                        'nombre' => $nombreSubcategoria,
                    ]);
                }
            }
        }
    }
}
