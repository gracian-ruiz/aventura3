<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en la Confirmación</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">

    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="w-full max-w-2xl rounded-xl border border-red-300 bg-red-50 text-center shadow-sm p-8">
            <h1 class="app-title text-red-700 mb-4">Error en la Confirmación</h1>
            <p class="text-red-800 text-lg">{{ $mensaje }}</p>
            <p class="text-red-700 mt-2">Llamanos o envia un WhatsApp a este numero 699 99 99 99</p>
        </div>
    </div>

</body>
</html>
