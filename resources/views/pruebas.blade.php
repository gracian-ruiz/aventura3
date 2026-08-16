<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba WhatsApp Cloud API</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(22, 131, 79, 0.12), transparent 34%),
                radial-gradient(circle at bottom right, rgba(13, 56, 38, 0.12), transparent 30%),
                linear-gradient(135deg, #f3f8f5 0%, #e2f1e8 100%);
        }

        .card {
            width: min(640px, calc(100vw - 32px));
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(13, 56, 38, 0.16);
            border: 1px solid rgba(16, 88, 52, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            color: #0f5132;
            font-size: 28px;
        }

        p {
            margin: 0 0 18px;
            color: #355248;
            line-height: 1.55;
        }

        .meta {
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 18px;
            background: #f4fbf7;
            border: 1px solid #d5eadf;
            color: #254638;
        }

        .alert {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .alert-success {
            background: #e9fff2;
            color: #0f5132;
            border: 1px solid #b7e6c9;
        }

        .alert-error {
            background: #fff1f1;
            color: #8a1c1c;
            border: 1px solid #f0b7b7;
        }

        form {
            margin-top: 10px;
        }

        button {
            border: 0;
            border-radius: 999px;
            padding: 14px 22px;
            background: linear-gradient(135deg, #16834f 0%, #0f6a3f 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(22, 131, 79, 0.28);
        }

        button:hover {
            filter: brightness(0.96);
        }

        small {
            display: block;
            margin-top: 16px;
            color: #5d7469;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Prueba de WhatsApp Cloud API</h1>
        <p>Este botón envía un mensaje de prueba a +31 637319765 usando la API de Meta.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="meta">
            <strong>Destino:</strong> {{ $targetNumber }}<br>
            <strong>Webhook Meta:</strong> {{ $webhookUrl }}<br>
            <strong>Verify token:</strong> {{ $verifyToken ?: 'Pendiente en .env' }}
        </div>

        <form method="POST" action="{{ route('pruebas.whatsapp.send') }}">
            @csrf
            <button type="submit">Enviar mensaje</button>
        </form>

        <small>
            Completa en tu .env: WHATSAPP_ACCESS_TOKEN, WHATSAPP_PHONE_NUMBER_ID, WHATSAPP_BUSINESS_ACCOUNT_ID y WHATSAPP_VERIFY_TOKEN.
        </small>
    </div>
</body>
</html>
