<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'whatsapp' => [
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'notice_email_gate' => env('WHATSAPP_NOTICE_EMAIL_GATE', 'gracianmiguel1995@gmail.com'),
        'notice_email_gate_list' => env('WHATSAPP_NOTICE_EMAIL_GATE_LIST', 'gracianmiguel1995@gmail.com,graciancristales@hotmail.com'),
        'notice_phone_gate' => env('WHATSAPP_NOTICE_PHONE_GATE', '34637319765'),
        'conversation_templates' => [
            [
                'key' => 'seguir_reparacion',
                'label' => 'Seguir reparacion',
                'template_name' => env('WHATSAPP_TEMPLATE_SEGUIR_REPARACION', 'seguir_reparacion'),
                'language' => env('WHATSAPP_TEMPLATE_SEGUIR_REPARACION_LANG', 'es'),
                'uses_customer_name' => true,
            ],
            [
                'key' => 'conversacion_problema_taller',
                'label' => 'Problema detectado en taller (con imagen)',
                'template_name' => env('WHATSAPP_TEMPLATE_CONVERSACION_PROBLEMA_TALLER', env('WHATSAPP_TEMPLATE_NUEVO_PROBLEMA', 'conversacion_problema_taller')),
                'language' => env('WHATSAPP_TEMPLATE_CONVERSACION_PROBLEMA_TALLER_LANG', env('WHATSAPP_TEMPLATE_NUEVO_PROBLEMA_LANG', 'es')),
                'uses_customer_name' => true,
                'uses_issue_area' => true,
                'uses_customer_reply_request' => true,
                'allows_header_image' => true,
                'requires_header_image' => true,
            ],
            [
                'key' => 'conversacion_problema_taller_sin_imagen',
                'label' => 'Problema detectado en taller (sin imagen)',
                'template_name' => env('WHATSAPP_TEMPLATE_CONVERSACION_PROBLEMA_TALLER_SIN_IMAGEN', 'conversacion_problema_taller_sin_imagen'),
                'language' => env('WHATSAPP_TEMPLATE_CONVERSACION_PROBLEMA_TALLER_SIN_IMAGEN_LANG', env('WHATSAPP_TEMPLATE_CONVERSACION_PROBLEMA_TALLER_LANG', 'es')),
                'uses_customer_name' => true,
                'uses_issue_area' => true,
                'uses_customer_reply_request' => true,
                'allows_header_image' => false,
                'requires_header_image' => false,
            ],
            [
                'key' => 'enviar_fotos_pieza',
                'label' => 'Enviar fotos de la pieza',
                'template_name' => env('WHATSAPP_TEMPLATE_ENVIAR_FOTOS_PIEZA', 'enviar_fotos_pieza'),
                'language' => env('WHATSAPP_TEMPLATE_ENVIAR_FOTOS_PIEZA_LANG', 'es'),
                'uses_customer_name' => true,
            ],
        ],
    ],

];
