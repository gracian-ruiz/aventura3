<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // La ruta / redirige al login si no hay sesión activa
        $response = $this->get('/');

        $response->assertRedirect();
    }
}
