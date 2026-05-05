<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComponentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_index_muestra_listado_de_componentes(): void
    {
        $user = $this->adminUser();
        $response = $this->actingAs($user)->get(route('components.index'));
        $response->assertStatus(200);
    }

    public function test_store_crea_componente_correctamente(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('components.store'), [
            'nombre'         => 'Frenos',
            'descripcion'    => 'Frenos de disco',
            'fecha_preaviso' => 5,
            'fecha_revision' => 60,
            'hora_taller'    => 2,
            'precio'         => 25.00,
        ]);

        $response->assertRedirect(route('components.index'));
        $this->assertDatabaseHas('components', ['nombre' => 'Frenos']);
    }

    public function test_store_falla_con_datos_invalidos(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('components.store'), [
            'nombre' => '',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_update_modifica_componente(): void
    {
        $user = $this->adminUser();
        $component = Component::factory()->create();

        $response = $this->actingAs($user)->put(route('components.update', $component), [
            'nombre'         => 'Frenos Actualizados',
            'descripcion'    => 'Descripción actualizada',
            'fecha_preaviso' => 10,
            'fecha_revision' => 90,
            'hora_taller'    => 3,
            'precio'         => 30.00,
        ]);

        $response->assertRedirect(route('components.index'));
        $this->assertDatabaseHas('components', ['nombre' => 'Frenos Actualizados']);
    }

    public function test_destroy_elimina_componente(): void
    {
        $user = $this->adminUser();
        $component = Component::factory()->create();

        $response = $this->actingAs($user)->delete(route('components.destroy', $component));

        $response->assertRedirect(route('components.index'));
        $this->assertDatabaseMissing('components', ['id' => $component->id]);
    }

    public function test_usuario_no_autenticado_es_redirigido(): void
    {
        $response = $this->get(route('components.index'));
        $response->assertRedirect('/login');
    }
}
