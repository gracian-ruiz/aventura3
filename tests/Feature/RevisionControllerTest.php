<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RevisionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_index_muestra_revisiones_de_bicicleta(): void
    {
        $user = $this->adminUser();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('bikes.revisions.index', $bike));
        $response->assertStatus(200);
    }

    public function test_store_crea_revision_correctamente(): void
    {
        $user      = $this->adminUser();
        $bike      = Bike::factory()->create(['user_id' => $user->id]);
        $component = Component::factory()->create();

        $response = $this->actingAs($user)->post(route('bikes.revisions.store', $bike), [
            'componente_id'    => $component->id,
            'fecha_revision'   => now()->format('Y-m-d'),
            'descripcion'      => 'Revisión de frenos',
            'tipo_fecha'       => 'opcional',
            'proxima_revision' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('bikes.revisions.index', $bike));
        $this->assertDatabaseHas('revisions', ['descripcion' => 'Revisión de frenos']);
    }

    public function test_store_tipo_fija_calcula_proxima_revision(): void
    {
        $user      = $this->adminUser();
        $bike      = Bike::factory()->create(['user_id' => $user->id]);
        $component = Component::factory()->create(['fecha_revision' => 3]);

        $response = $this->actingAs($user)->post(route('bikes.revisions.store', $bike), [
            'componente_id'  => $component->id,
            'fecha_revision' => now()->format('Y-m-d'),
            'descripcion'    => 'Revisión fija',
            'tipo_fecha'     => 'fija',
        ]);

        $response->assertRedirect(route('bikes.revisions.index', $bike));
        $this->assertDatabaseHas('revisions', ['descripcion' => 'Revisión fija']);
    }

    public function test_store_falla_con_datos_invalidos(): void
    {
        $user = $this->adminUser();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('bikes.revisions.store', $bike), [
            'descripcion' => '',
        ]);

        $response->assertSessionHasErrors('componente_id');
    }

    public function test_update_modifica_revision(): void
    {
        $user      = $this->adminUser();
        $bike      = Bike::factory()->create(['user_id' => $user->id]);
        $component = Component::factory()->create();
        $revision  = Revision::factory()->create(['bike_id' => $bike->id, 'componente_id' => $component->id]);

        $response = $this->actingAs($user)->put(route('bikes.revisions.update', [$bike, $revision]), [
            'componente_id'  => $component->id,
            'fecha_revision' => now()->format('Y-m-d'),
            'descripcion'    => 'Descripción actualizada',
        ]);

        $response->assertRedirect(route('bikes.revisions.index', $bike));
        $this->assertDatabaseHas('revisions', ['descripcion' => 'Descripción actualizada']);
    }

    public function test_destroy_elimina_revision(): void
    {
        $user      = $this->adminUser();
        $bike      = Bike::factory()->create(['user_id' => $user->id]);
        $component = Component::factory()->create();
        $revision  = Revision::factory()->create(['bike_id' => $bike->id, 'componente_id' => $component->id]);

        $response = $this->actingAs($user)->delete(route('bikes.revisions.destroy', [$bike, $revision]));

        $response->assertRedirect(route('bikes.revisions.index', $bike));
        $this->assertDatabaseMissing('revisions', ['id' => $revision->id]);
    }

    public function test_usuario_no_autenticado_es_redirigido(): void
    {
        $bike = Bike::factory()->create();
        $response = $this->get(route('bikes.revisions.index', $bike));
        $response->assertRedirect('/login');
    }
}
