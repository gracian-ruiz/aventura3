<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Revision;
use App\Models\Bike;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RevisionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function una_revision_pertenece_a_un_componente()
    {
        $componente = Component::factory()->create();
        $revision = Revision::factory()->create(['componente_id' => $componente->id]);

        $this->assertInstanceOf(Component::class, $revision->componente);
    }
}
