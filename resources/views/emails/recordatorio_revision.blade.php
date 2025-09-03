<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recordatorio de revisión</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; line-height:1.5; color:#222;">
    <h2 style="margin:0 0 .5rem 0;">Hola {{ $user ?? 'cliente' }},</h2>

    <p>
        Te recordamos la próxima revisión de
        <strong>{{ $comp ?? 'componente' }}</strong>
        de tu bicicleta
        <strong>
            @if(!empty($marca)) {{ $marca }} @endif
            {{ $bike ?? '' }}
        </strong>
        prevista para el <strong>{{ $fecha }}</strong>.
    </p>

    @if(!empty($compDesc))
        <p style="margin-top: 1rem;">
            <strong>Detalle del componente:</strong><br>
            {!! nl2br(e($compDesc)) !!}
        </p>
    @endif

    <p>
        Si necesitas cambiar la fecha o tienes cualquier duda, respóndenos a este correo.
    </p>

    <p>¡Gracias por confiar en Aventura Bike! 🚴</p>
</body>
</html>
