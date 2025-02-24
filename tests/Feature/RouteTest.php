<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Carbon\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function la_ruta_dashboard_responde_correctamente()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard'); // Ajusta según el contenido de la vista
    }

    /** @test */
    public function la_ruta_de_bicicletas_muestra_el_listado()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/bikes');

        $response->assertStatus(200);
        $response->assertSee('Bicicletas'); // Ajusta según el contenido de la vista
    }

    /** @test */
    public function test_se_puede_crear_una_nueva_bicicleta()
    {
        $user = User::factory()->create(['role' => 'admin']); // Asegurar que es administrador
        
        $response = $this->actingAs($user)->post(route('bikes.store'), [
            'nombre' => 'Trek X-Caliber',
            'marca' => 'Trek',
            'anio_modelo' => 2023,
            'user_id' => $user->id, // ✅ PASAR EL ID DEL USUARIO
        ]);
    
        $response->assertRedirect(route('bikes.index')); // Verificar la redirección correcta
        $this->assertDatabaseHas('bikes', ['nombre' => 'Trek X-Caliber']);
    }
    
    

    /** @test */
    public function la_ruta_de_revisiones_muestra_el_listado()
    {
        $user = User::factory()->create();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/bikes/{$bike->id}/revisions");

        $response->assertStatus(200);
        $response->assertSee('Revisiones'); // Ajusta según el contenido de la vista
    }

    /** @test */
    public function se_puede_crear_una_nueva_revision()
    {
        $user = User::factory()->create();
        $bike = Bike::factory()->create(['user_id' => $user->id]);
        $componente = Component::factory()->create();

        $response = $this->actingAs($user)->post("/bikes/{$bike->id}/revisions", [
            'componente_id' => $componente->id,
            'fecha_revision' => now()->format('Y-m-d'),
            'descripcion' => 'Cambio de frenos',
            'proxima_revision' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect("/bikes/{$bike->id}/revisions");
        $this->assertDatabaseHas('revisions', ['descripcion' => 'Cambio de frenos']);
    }

    /** @test */
    public function la_ruta_de_aviso_enviados_muestra_el_listado()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/avisos-enviados');

        $response->assertStatus(200);
        $response->assertSee('Avisos Enviados'); // Ajusta según la vista real
    }
    /** @test */
    public function la_ruta_de_los_componentes(){

        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/components');

        $response->assertStatus(200);
        $response->assertSee('Gestión de Componentes');
    }
/** @test */
public function un_usuario_puede_crear_un_componente_nuevo()
{
    // 🔒 Creamos un usuario autenticado (que pueda acceder a la ruta)
    $user = User::factory()->create();

    // 🚀 Simulamos el POST para crear el componente
    $response = $this->actingAs($user)->post(route('components.store'), [
        'nombre' => 'Horquilla',
        'fecha_preaviso' => 1,
    ]);

    // 📌 Verificamos que redirige correctamente
    $response->assertRedirect(route('components.index'));

    // ✅ Verificamos que el componente se creó en la base de datos
    $this->assertDatabaseHas('components', [
        'nombre' => 'Horquilla',
        'fecha_preaviso' => 1,
    ]);

    // 🔍 (Opcional) Verificamos que al visitar la lista aparece el nuevo componente
    $response = $this->actingAs($user)->get(route('components.index'));
    $response->assertSee('Horquilla');
}

}
