<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_index_muestra_listado_de_usuarios(): void
    {
        $user = $this->adminUser();
        $response = $this->actingAs($user)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_store_crea_usuario_correctamente(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name'                  => 'Nuevo Usuario',
            'email'                 => 'nuevo@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'user',
            'telefono'              => '600000000',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);
    }

    public function test_store_falla_con_email_duplicado(): void
    {
        $admin = $this->adminUser();
        User::factory()->create(['email' => 'duplicado@test.com']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name'                  => 'Otro',
            'email'                 => 'duplicado@test.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'user',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_modifica_usuario(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name'     => 'Nombre Cambiado',
            'email'    => $target->email,
            'role'     => 'user',
            'telefono' => '611111111',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['name' => 'Nombre Cambiado']);
    }

    public function test_destroy_elimina_usuario(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_usuario_no_autenticado_es_redirigido(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect('/login');
    }
}
