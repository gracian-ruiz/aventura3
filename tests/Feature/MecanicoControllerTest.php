<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Appointment;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MecanicoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $mecanico;
    private Bike $bike;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mecanico = User::factory()->create(['role' => 'admin']);
        $this->bike      = Bike::factory()->create(['user_id' => $this->mecanico->id]);
        $this->appointment = Appointment::create([
            'bike_id'               => $this->bike->id,
            'user_id'               => $this->mecanico->id,
            'estado'                => 'pendiente',
            'prioridad'             => 'normal',
            'estimacion_reparacion' => '',
        ]);
    }

    // ─── complete ────────────────────────────────────────────────────────────

    /** @test */
    public function complete_crea_revisiones_y_marca_como_completada()
    {
        $componente = Component::factory()->create(['fecha_revision' => 180]);

        $response = $this->actingAs($this->mecanico)
            ->put(route('mecanico.complete', $this->appointment), [
                'revisiones'                                => [$componente->id],
                'descripcion_revisiones'                    => [$componente->id => 'Revisión completa'],
                'tipo_fecha'                                => [$componente->id => 'fija'],
            ]);

        $response->assertRedirect(route('mecanico.index'));
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->appointment->id,
            'estado' => 'completada',
        ]);
        $this->assertDatabaseHas('revisions', [
            'componente_id' => $componente->id,
            'descripcion'   => 'Revisión completa',
        ]);
    }

    /** @test */
    public function complete_hace_rollback_si_falla_y_no_deja_datos_parciales()
    {
        // Pasamos un componente que no existe → falla la validación
        $response = $this->actingAs($this->mecanico)
            ->put(route('mecanico.complete', $this->appointment), [
                'revisiones'             => [99999],
                'descripcion_revisiones' => [99999 => 'Test'],
                'tipo_fecha'             => [99999 => 'fija'],
            ]);

        // Debe fallar validación (422) o redirigir con error — en ningún caso 500
        $this->assertNotEquals(500, $response->getStatusCode());
        // El estado sigue siendo pendiente
        $this->assertDatabaseHas('appointments', [
            'id'     => $this->appointment->id,
            'estado' => 'pendiente',
        ]);
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    /** @test */
    public function destroy_elimina_cita_pendiente()
    {
        $id = $this->appointment->id;

        $response = $this->actingAs($this->mecanico)
            ->delete(route('mecanico.destroy', $this->appointment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('appointments', ['id' => $id]);
    }

    /** @test */
    public function destroy_cita_completada_redirige_a_historico()
    {
        $this->appointment->update(['estado' => 'completada']);

        $response = $this->actingAs($this->mecanico)
            ->delete(route('mecanico.destroy', $this->appointment));

        $response->assertRedirect(route('appointments.historico'));
    }

    // ─── updatedos ───────────────────────────────────────────────────────────

    /** @test */
    public function updatedos_actualiza_prioridad_y_componentes()
    {
        $componente = Component::factory()->create();

        $response = $this->actingAs($this->mecanico)
            ->put(route('mecanico.updatedos', $this->appointment), [
                'bike_id'      => $this->bike->id,
                'componentes'  => [$componente->id],
                'horas_trabajo'=> [60],
                'precio'       => [50.00],
                'prioridad'    => 'urgente',
                'descuento'    => [0],
            ]);

        $response->assertRedirect(route('mecanico.index'));
        $this->assertDatabaseHas('appointments', [
            'id'       => $this->appointment->id,
            'prioridad'=> 'urgente',
        ]);
        $this->assertDatabaseHas('appointment_component', [
            'appointment_id' => $this->appointment->id,
            'componente_id'  => $componente->id,
            'horas_trabajo'  => 60,
        ]);
    }

    // ─── No autenticado ───────────────────────────────────────────────────────

    /** @test */
    public function usuario_no_autenticado_es_redirigido_al_login()
    {
        $response = $this->get(route('mecanico.index'));
        $response->assertRedirect('/login');
    }
}
