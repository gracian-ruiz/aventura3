<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Appointment;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresupuestoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Bike $bike;
    private Appointment $presupuesto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->bike  = Bike::factory()->create(['user_id' => $this->admin->id]);
        $this->presupuesto = Appointment::create([
            'bike_id'               => $this->bike->id,
            'user_id'               => $this->admin->id,
            'estado'                => 'presupuesto',
            'prioridad'             => 'normal',
            'token_presupuesto'     => Str::random(32),
            'horas_total'           => 0,
            'precio_total'          => 0,
            'estimacion_reparacion' => '',
        ]);
    }

    // ─── index ───────────────────────────────────────────────────────────────

    /** @test */
    public function index_muestra_listado_de_presupuestos()
    {
        $response = $this->actingAs($this->admin)->get(route('presupuestos.index'));
        $response->assertStatus(200);
    }

    // ─── factura ─────────────────────────────────────────────────────────────

    /** @test */
    public function factura_devuelve_vista_con_datos_del_presupuesto()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('presupuestos.factura', $this->presupuesto->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function factura_devuelve_404_si_no_existe()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('presupuestos.factura', 99999));

        $response->assertStatus(404);
    }

    // ─── update ──────────────────────────────────────────────────────────────

    /** @test */
    public function update_actualiza_los_componentes_del_presupuesto()
    {
        $componente = Component::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('presupuestos.update', $this->presupuesto->id), [
                'bike_id'      => $this->bike->id,
                'componentes'  => [$componente->id],
                'horas_trabajo'=> [60],
                'precio'       => [100.00],
                'precio_material' => [25.50],
                'descuento'    => [0],
                'prioridad'    => 'urgente',
            ]);

        $response->assertRedirect(route('presupuestos.index'));
        $this->assertDatabaseHas('appointments', [
            'id'          => $this->presupuesto->id,
            'precio_total'=> 125.5,
            'horas_total' => 60,
        ]);
        $this->assertDatabaseHas('appointment_component', [
            'appointment_id' => $this->presupuesto->id,
            'componente_id'  => $componente->id,
            'precio_material'=> 25.5,
        ]);
    }

    /** @test */
    public function update_hace_rollback_si_el_bike_id_no_existe()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('presupuestos.update', $this->presupuesto->id), [
                'bike_id' => 99999, // no existe → falla validación
            ]);

        // Validación rechaza → redirige de vuelta con errores
        $response->assertRedirect();
        $response->assertSessionHasErrors('bike_id');
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    /** @test */
    public function destroy_elimina_presupuesto_y_sus_componentes()
    {
        $componente = Component::factory()->create();
        DB::table('appointment_component')->insert([
            'appointment_id' => $this->presupuesto->id,
            'componente_id'  => $componente->id,
            'horas_trabajo'  => 30,
            'total_precio'   => 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $id = $this->presupuesto->id;
        $response = $this->actingAs($this->admin)
            ->delete(route('presupuestos.destroy', $id));

        $response->assertRedirect(route('presupuestos.index'));
        $this->assertDatabaseMissing('appointments', ['id' => $id]);
        $this->assertDatabaseMissing('appointment_component', ['appointment_id' => $id]);
    }

    // ─── confirmarPresupuesto (token público) ────────────────────────────────

    /** @test */
    public function confirmarPresupuesto_muestra_vista_con_token_valido()
    {
        $response = $this->get("/confirmacion/presupuesto/{$this->presupuesto->id}?token={$this->presupuesto->token_presupuesto}");

        $response->assertStatus(200);
    }

    /** @test */
    public function confirmarPresupuesto_devuelve_error_con_token_invalido()
    {
        $response = $this->get("/confirmacion/presupuesto/{$this->presupuesto->id}?token=token_falso");

        $response->assertStatus(403);
    }

    /** @test */
    public function procesarConfirmacion_aprueba_presupuesto_y_cambia_estado()
    {
        $response = $this->post(
            "/confirmacion/presupuesto/{$this->presupuesto->id}?token={$this->presupuesto->token_presupuesto}",
            ['accion' => 'aprobado']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->presupuesto->id,
            'estado' => 'pendiente',
        ]);
    }

    /** @test */
    public function procesarConfirmacion_rechaza_presupuesto()
    {
        $response = $this->post(
            "/confirmacion/presupuesto/{$this->presupuesto->id}?token={$this->presupuesto->token_presupuesto}",
            ['accion' => 'denegado']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->presupuesto->id,
            'estado' => 'denegado',
        ]);
    }

    // ─── No autenticado ───────────────────────────────────────────────────────

    /** @test */
    public function usuario_no_autenticado_es_redirigido_al_login()
    {
        $response = $this->get(route('presupuestos.index'));
        $response->assertRedirect('/login');
    }
}
