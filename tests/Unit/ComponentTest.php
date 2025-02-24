<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Component;
use App\Models\Revision;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
/** @test */
    public function un_componente_puede_tener_muchas_revisiones()
    {
        $componente = Component::factory()->create();
        $revision1 = Revision::factory()->create(['componente_id' => $componente->id]);
        $revision2 = Revision::factory()->create(['componente_id' => $componente->id]);

        $componente->load('revisiones'); // 🔥 Cargar relación antes de hacer el assert

        $this->assertCount(2, $componente->revisiones);
        $this->assertTrue($componente->revisiones->contains($revision1));
        $this->assertTrue($componente->revisiones->contains($revision2));
    }


    /** @test */
    public function una_revision_pertenece_a_un_componente()
    {
        $componente = Component::factory()->create();
        $revision = Revision::factory()->create(['componente_id' => $componente->id]);

        $this->assertInstanceOf(Component::class, $revision->componente);
    }
}
