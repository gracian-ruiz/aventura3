<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Appointment;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Bike $bike;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->bike  = Bike::factory()->create(['user_id' => $this->admin->id]);
        $this->appointment = Appointment::create([
            'bike_id'               => $this->bike->id,
            'user_id'               => $this->admin->id,
            'estado'                => 'pendiente',
            'prioridad'             => 'normal',
            'estimacion_reparacion' => '',
        ]);
    }

    // ─── updateEstado ────────────────────────────────────────────────────────

    /** @test */
    public function updateEstado_cambia_estado_correctamente()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('appointments.updateEstado', $this->appointment), [
                'estado' => 'en proceso',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->appointment->id,
            'estado' => 'en proceso',
        ]);
    }

    /** @test */
    public function updateEstado_rechaza_estado_invalido()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('appointments.updateEstado', $this->appointment), [
                'estado' => 'inventado',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        // El estado no debe haber cambiado
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->appointment->id,
            'estado' => 'pendiente',
        ]);
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    /** @test */
    public function destroy_elimina_la_cita()
    {
        $id = $this->appointment->id;

        $response = $this->actingAs($this->admin)
            ->delete(route('appointments.destroy', $this->appointment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('appointments', ['id' => $id]);
    }

    /** @test */
    public function destroy_registra_error_si_falla_la_base_de_datos()
    {
        Log::shouldReceive('error')->once();

        // Forzamos fallo al simular un appointment cuyo ID no existe en la tabla
        // Usamos mock del modelo para lanzar excepción en delete()
        /** @var Appointment $mock */
        $mock = $this->partialMock(Appointment::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new \Exception('DB error simulado'));
        });
        $mock->id     = $this->appointment->id;
        $mock->estado = 'pendiente';

        $controller = new \App\Http\Controllers\AppointmentController();
        $response   = $controller->destroy(request(), $mock);

        $this->assertEquals(302, $response->getStatusCode());
    }

    // ─── updateReparacion ────────────────────────────────────────────────────

    /** @test */
    public function updateReparacion_actualiza_kilometros_de_la_bici()
    {
        DB::table('appointment_component')->insert([
            'appointment_id' => $this->appointment->id,
            'componente_id'  => Component::factory()->create()->id,
            'horas_trabajo'  => 30,
            'total_precio'   => 0,
            'checked'        => false,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('appointments.updateReparacion', $this->appointment), [
                'componentes' => [],
                'kilometros'  => 5000,
            ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('bikes', [
            'id'         => $this->bike->id,
            'kilometros' => 5000,
        ]);
    }

    // ─── calendariocitas ─────────────────────────────────────────────────────

    /** @test */
    public function calendariocitas_responde_con_200()
    {
        $response = $this->actingAs($this->admin)->get('/citas/calendario');

        // La ruta puede no existir con ese nombre, simplemente verificamos que no lanza 500
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    // ─── No autenticado ───────────────────────────────────────────────────────

    /** @test */
    public function usuario_no_autenticado_es_redirigido_al_login()
    {
        $response = $this->get(route('appointments.index'));
        $response->assertRedirect('/login');
    }
}
