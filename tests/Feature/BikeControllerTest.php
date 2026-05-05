<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BikeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_index_muestra_listado_de_bicicletas(): void
    {
        $user = $this->adminUser();
        $response = $this->actingAs($user)->get(route('bikes.index'));
        $response->assertStatus(200);
    }

    public function test_store_crea_bicicleta_correctamente(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('bikes.store'), [
            'user_id'     => $user->id,
            'nombre'      => 'MTB Pro',
            'marca'       => 'Trek',
            'anio_modelo' => 2022,
            'kilometros'  => 100,
            'color'       => 'rojo',
        ]);

        $response->assertRedirect(route('bikes.index'));
        $this->assertDatabaseHas('bikes', ['nombre' => 'MTB Pro', 'marca' => 'Trek']);
    }

    public function test_store_falla_con_datos_invalidos(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->post(route('bikes.store'), [
            'nombre' => '',
        ]);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_update_modifica_bicicleta(): void
    {
        $user = $this->adminUser();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('bikes.update', $bike), [
            'user_id'     => $user->id,
            'nombre'      => 'Nombre Actualizado',
            'marca'       => 'Specialized',
            'anio_modelo' => 2023,
            'kilometros'  => 200,
            'color'       => 'azul',
        ]);

        $response->assertRedirect(route('bikes.index'));
        $this->assertDatabaseHas('bikes', ['nombre' => 'Nombre Actualizado']);
    }

    public function test_destroy_elimina_bicicleta(): void
    {
        $user = $this->adminUser();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('bikes.destroy', $bike));

        $response->assertRedirect(route('bikes.index'));
        $this->assertDatabaseMissing('bikes', ['id' => $bike->id]);
    }

    public function test_usuario_no_autenticado_es_redirigido(): void
    {
        $response = $this->get(route('bikes.index'));
        $response->assertRedirect('/login');
    }
}
