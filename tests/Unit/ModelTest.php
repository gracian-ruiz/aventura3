<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bike;
use App\Models\Revision;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase; // Limpia la base de datos después de cada prueba

    /** @test */
    public function un_usuario_puede_tener_muchas_bicicletas()
    {
        $user = User::factory()->create();
        $bike1 = Bike::factory()->create(['user_id' => $user->id]);
        $bike2 = Bike::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->bikes);
        $this->assertTrue($user->bikes->contains($bike1));
        $this->assertTrue($user->bikes->contains($bike2));
    }

    /** @test */
    public function una_bicicleta_pertenece_a_un_usuario()
    {
        $user = User::factory()->create();
        $bike = Bike::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $bike->user);
    }

    /** @test */
    public function una_revision_pertenece_a_una_bicicleta()
    {
        $bike = Bike::factory()->create();
        $revision = Revision::factory()->create(['bike_id' => $bike->id]);

        $this->assertInstanceOf(Bike::class, $revision->bike);
    }
}
