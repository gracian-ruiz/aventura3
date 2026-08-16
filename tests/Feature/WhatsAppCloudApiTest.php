<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function webhook_de_meta_devuelve_el_challenge_con_token_valido()
    {
        config()->set('services.whatsapp.verify_token', 'token-de-prueba');

        $response = $this->get('/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=token-de-prueba&hub.challenge=12345');

        $response->assertOk();
        $response->assertSeeText('12345');
    }

    /** @test */
    public function un_admin_puede_enviar_un_mensaje_de_prueba_por_whatsapp_cloud_api()
    {
        config()->set('services.whatsapp.access_token', 'token-permanente');
        config()->set('services.whatsapp.phone_number_id', '123456789');

        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messages' => [
                    ['id' => 'wamid.test'],
                ],
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('pruebas.whatsapp.send'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
                && $request->hasHeader('Authorization', 'Bearer token-permanente')
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '31637319765'
                && $request['type'] === 'text';
        });
    }
}