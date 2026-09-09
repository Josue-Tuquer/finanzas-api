<?php

namespace Tests\Feature;

use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_monthly_and_year_to_date_totals_for_the_authenticated_user_in_one_query(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Ingreso::factory()->for($user)->create(['fecha' => '2026-01-10', 'monto' => '1000.00']);
        Ingreso::factory()->for($user)->create(['fecha' => '2026-03-05', 'monto' => '500.00']);
        Ingreso::factory()->for($user)->create(['fecha' => '2026-04-01', 'monto' => '900.00']);
        Egreso::factory()->for($user)->create(['fecha' => '2026-02-12', 'monto' => '100.00']);
        Egreso::factory()->for($user)->create(['fecha' => '2026-03-15', 'monto' => '150.00']);
        Egreso::factory()->for($user)->create(['fecha' => '2026-07-15', 'monto' => '800.00']);
        Ingreso::factory()->for($otherUser)->create(['fecha' => '2026-03-10', 'monto' => '9999.00']);

        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/resumen?anio=2026&mes=3');

        $response->assertOk()->assertJson([
            'ingresos_mes' => '500',
            'egresos_mes' => '150',
            'balance_mes' => '350',
            'ingresos_acumulados' => '1500',
            'egresos_acumulados' => '250',
            'balance_acumulado' => '1250',
            'porcentaje_gastado' => '30',
        ]);
        $this->assertSame(1, $queries);
    }

    public function test_returns_zeroes_when_there_are_no_records_or_monthly_income_is_zero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard/resumen?anio=2026&mes=3')
            ->assertOk()
            ->assertJson([
                'ingresos_mes' => '0',
                'egresos_mes' => '0',
                'balance_mes' => '0',
                'ingresos_acumulados' => '0',
                'egresos_acumulados' => '0',
                'balance_acumulado' => '0',
                'porcentaje_gastado' => '0',
            ]);
    }
}
