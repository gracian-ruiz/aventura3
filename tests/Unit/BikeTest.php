<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bike;
use App\Models\User;
use App\Models\Revision;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function una_bicicleta_puede_tener_muchas_revisiones()
    {
        $bike = Bike::factory()->create();
        $revision1 = Revision::factory()->create(['bike_id' => $bike->id]);
        $revision2 = Revision::factory()->create(['bike_id' => $bike->id]);

        $bike->load('revisions'); // ⚡ Cargar relación

        $this->assertCount(2, $bike->revisions);
        $this->assertTrue($bike->revisions->contains($revision1));
        $this->assertTrue($bike->revisions->contains($revision2));
    }
}
