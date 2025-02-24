<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AvisoEnviado;
use App\Models\User;
use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AvisoEnviadoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_aviso_se_registra_correctamente()
    {
        $user = User::factory()->create([
            'telefono' => '637319765' // 📞 Agregar un número de teléfono
        ]);

        $bike = Bike::factory()->create(['user_id' => $user->id]);
        $componente = Component::factory()->create();
        $revision = Revision::factory()->create(['bike_id' => $bike->id, 'componente_id' => $componente->id]);

        $aviso = AvisoEnviado::create([
            'user_id' => $user->id,
            'bike_id' => $bike->id,
            'revision_id' => $revision->id,
            'componente_id' => $componente->id,
            'telefono' => $user->telefono,
            'mensaje' => "Prueba de mensaje",
            'enviado_en' => now(),
        ]);

        $this->assertDatabaseHas('avisos_enviados', [
            'user_id' => $user->id,
            'bike_id' => $bike->id,
            'revision_id' => $revision->id,
            'componente_id' => $componente->id,
            'telefono' => $user->telefono,
            'mensaje' => "Prueba de mensaje",
        ]);
    }
}
