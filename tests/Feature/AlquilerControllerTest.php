<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alquiler;
use App\Models\UsuarioAlquiler;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AlquilerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private UsuarioAlquiler $usuarioAlquiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->usuarioAlquiler = UsuarioAlquiler::create([
            'nombre'   => 'Cliente Test',
            'dni'      => '12345678A',
            'telefono' => '600000000',
            'email'    => 'cliente@test.com',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearMaterial(array $overrides = []): Material
    {
        return Material::create(array_merge([
            'nombre'        => 'Bici Test',
            'tipo'          => 'mtb',
            'talla'         => 'M',
            'precio_dia'    => 20.00,
            'reserva_precio'=> 50.00,
            'disponible'    => true,
        ], $overrides));
    }

    private function payloadStore(Material $material): array
    {
        return [
            'usuario_id'   => $this->usuarioAlquiler->id,
            'fecha_inicio' => now()->addDay()->toDateString(),
            'fecha_fin'    => now()->addDays(3)->toDateString(),
            'estado'       => 'reservado',
            'materiales'   => [
                0 => [
                    'selected'        => 'on',
                    'id'              => $material->id,
                    'precio_unitario' => 60.00,
                    'descuento'       => 0,
                    'reserva_precio'  => 50.00,
                ],
            ],
        ];
    }

    // ─── store ───────────────────────────────────────────────────────────────

    /** @test */
    public function store_crea_alquiler_y_asocia_materiales()
    {
        $material = $this->crearMaterial();

        $response = $this->actingAs($this->admin)
            ->post(route('alquiler.store', $this->usuarioAlquiler), $this->payloadStore($material));

        $response->assertRedirect(route('alquileres.index'));
        $this->assertDatabaseHas('alquileres', [
            'usuario_id'  => $this->usuarioAlquiler->id,
            'total_precio'=> 60.00,
        ]);
        $this->assertDatabaseHas('alquiler_material', [
            'material_id' => $material->id,
        ]);
    }

    /** @test */
    public function store_falla_si_no_hay_materiales_seleccionados()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('alquiler.store', $this->usuarioAlquiler), [
                'usuario_id'   => $this->usuarioAlquiler->id,
                'fecha_inicio' => now()->addDay()->toDateString(),
                'fecha_fin'    => now()->addDays(3)->toDateString(),
                'materiales'   => [],
            ]);

        // Debe redirigir de vuelta con error de validación, no crear nada
        $response->assertRedirect();
        $this->assertDatabaseCount('alquileres', 0);
    }

    /** @test */
    public function store_hace_rollback_si_falla_al_guardar()
    {
        // Material con id inexistente para que attach falle
        $response = $this->actingAs($this->admin)
            ->post(route('alquiler.store', $this->usuarioAlquiler), [
                'usuario_id'   => $this->usuarioAlquiler->id,
                'fecha_inicio' => now()->addDay()->toDateString(),
                'fecha_fin'    => now()->addDays(3)->toDateString(),
                'materiales'   => [
                    0 => [
                        'selected'        => 'on',
                        'id'              => 99999,   // no existe
                        'precio_unitario' => 60.00,
                        'descuento'       => 0,
                        'reserva_precio'  => 50.00,
                    ],
                ],
            ]);

        // No deben quedar alquileres huérfanos en la BD
        $this->assertDatabaseCount('alquileres', 0);
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    // ─── update ──────────────────────────────────────────────────────────────

    /** @test */
    public function update_modifica_estado_del_alquiler()
    {
        $material = $this->crearMaterial();
        $alquiler = Alquiler::create([
            'usuario_id'  => $this->usuarioAlquiler->id,
            'fecha_inicio'=> now()->toDateString(),
            'fecha_fin'   => now()->addDays(2)->toDateString(),
            'estado'      => 'reservado',
            'total_precio'=> 40.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('alquileres.update', $alquiler), [
                'estado'      => 'activo',
                'total_precio'=> 40.00,
                'descuento'   => 0,
            ]);

        $response->assertRedirect(route('alquileres.index'));
        $this->assertDatabaseHas('alquileres', [
            'id'    => $alquiler->id,
            'estado'=> 'activo',
        ]);
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    /** @test */
    public function destroy_elimina_alquiler_y_sus_materiales_pivot()
    {
        $material = $this->crearMaterial();
        $alquiler = Alquiler::create([
            'usuario_id'  => $this->usuarioAlquiler->id,
            'fecha_inicio'=> now()->toDateString(),
            'fecha_fin'   => now()->addDays(2)->toDateString(),
            'estado'      => 'activo',
            'total_precio'=> 40.00,
        ]);

        DB::table('alquiler_material')->insert([
            'alquiler_id' => $alquiler->id,
            'material_id' => $material->id,
            'fecha_inicio'=> now()->toDateString(),
            'fecha_fin'   => now()->addDays(2)->toDateString(),
            'precio_unitario' => 20.00,
            'subtotal'    => 40.00,
            'descuento'   => 0,
            'reserva_precio' => 50.00,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $id = $alquiler->id;
        $response = $this->actingAs($this->admin)
            ->delete('/alquileres/' . $id);

        $response->assertRedirect(route('alquileres.index'));
        $this->assertDatabaseMissing('alquileres', ['id' => $id]);
        $this->assertDatabaseMissing('alquiler_material', ['alquiler_id' => $id]);
    }

    // ─── No autenticado ───────────────────────────────────────────────────────

    /** @test */
    public function usuario_no_autenticado_es_redirigido_al_login()
    {
        $response = $this->get(route('alquileres.index'));
        $response->assertRedirect('/login');
    }
}
