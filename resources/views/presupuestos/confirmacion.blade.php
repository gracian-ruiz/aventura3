<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Presupuesto</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    @if (session('mensaje'))
        <div class="max-w-3xl mx-auto mt-6 px-4">
            <div class="p-4 rounded-lg border border-green-300 bg-green-100 text-green-700">
                {{ session('mensaje') }}
            </div>
        </div>
    @endif

    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="w-full max-w-4xl app-panel p-8 text-center">
            <h1 class="app-title text-sky-600 mb-4">Confirmación de Presupuesto</h1>
            <p class="text-slate-600 text-lg">Hola <strong>{{ $presupuesto->cliente_nombre }}</strong>,</p>
            <p class="text-slate-600 text-lg mt-2">
                Este es el presupuesto para la reparación de tu bicicleta <strong class="text-success">{{ $presupuesto->bicicleta_nombre }}</strong>.
            </p>
            <p class="text-slate-600 text-lg mt-2">Si aceptas, pasará al proceso de reparación lo antes posible.</p>

            <form action="{{ route('presupuesto.procesar', ['presupuestoId' => $presupuesto->id, 'token' => request('token')]) }}" method="POST">
                @csrf
                <div class="flex flex-col sm:flex-row justify-center gap-3 mt-6">
                    <button type="submit" name="accion" value="aprobado" class="app-btn app-btn-success text-base px-6">
                        Aceptar Presupuesto
                    </button>
                    <button type="submit" name="accion" value="denegado" class="app-btn app-btn-danger text-base px-6">
                        Rechazar Presupuesto
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
