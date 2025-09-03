<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Presupuesto</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #222;">
    <h2 style="margin-bottom: 0.5rem;">Hola {{ $presupuesto->usuario_nombre }},</h2>

    <p style="margin-top: 0;">
        Te enviamos el presupuesto de tu bicicleta
        <strong>
            @if(!empty($presupuesto->marca))
                {{ $presupuesto->marca }}
            @endif
            {{ $presupuesto->bicicleta_nombre }}
        </strong>.
    </p>

    {{-- Mensaje generado (respeta saltos de línea) --}}
    @isset($mensaje)
        <p>{!! nl2br(e($mensaje)) !!}</p>
    @endisset

    {{-- Botón de confirmación (HTML) --}}
    @isset($presupuestoUrl)
        <p style="margin: 1.25rem 0;">
            <a href="{{ $presupuestoUrl }}"
               style="display:inline-block;padding:12px 18px;text-decoration:none;border-radius:6px;
                      background:#0d6efd;color:#fff;font-weight:bold;">
                Confirmar presupuesto
            </a>
        </p>
        <p style="font-size: 12px; color: #666;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
            <span style="word-break: break-all;">{{ $presupuestoUrl }}</span>
        </p>
    @endisset

    <p>Adjuntamos el PDF con todos los detalles.</p>
    <p>¡Gracias por confiar en nosotros! 🚴</p>
</body>
</html>
