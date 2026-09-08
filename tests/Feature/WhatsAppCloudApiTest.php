<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
                && $request['to'] === '34637319765'
                && $request['type'] === 'text';
        });
    }

    /** @test */
    public function el_webhook_de_meta_acepta_status_failed_y_responde_ok()
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '2207697569983804',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'statuses' => [[
                            'id' => 'wamid.test',
                            'status' => 'failed',
                            'timestamp' => '1788804732',
                            'recipient_id' => '34637319765',
                            'errors' => [[
                                'code' => 131026,
                                'title' => 'Message Undeliverable',
                                'message' => 'No se pudo entregar el mensaje',
                            ]],
                        ]],
                    ],
                ]],
            ]],
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    /** @test */
    public function el_webhook_guarda_mensajes_entrantes_en_el_inbox()
    {
        Log::spy();

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '2207697569983804',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'metadata' => [
                            'display_phone_number' => '34600111222',
                        ],
                        'messages' => [[
                            'id' => 'wamid.inbound',
                            'from' => '34637319765',
                            'type' => 'text',
                            'text' => [
                                'body' => 'Hola',
                            ],
                        ]],
                    ],
                ]],
            ]],
        ];

        $response = $this->postJson('/api/whatsapp/webhook', $payload);

        $response->assertOk();
        $this->assertDatabaseHas('whatsapp_messages', [
            'wa_id' => 'wamid.inbound',
            'from_phone' => '34637319765',
            'to_phone' => '34600111222',
            'direction' => 'inbound',
            'body' => 'Hola',
        ]);
    }
}