<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Presupuesto</title>
    <link rel="stylesheet" href="path/to/tailwind.css">
    <style>
        /* Estilos personalizados */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9fafb; /* Fondo suave */
            color: #333;
            margin: 0;
            padding: 0;
        }

        .fullscreen-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .fullscreen-text {
            text-align: center;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
        }

        .btn {
            font-size: 1.25rem;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .alert {
            background-color: #e2e3e5;
            color: #383d41;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #007bff;
            font-weight: bold;
        }

        p {
            font-size: 1.25rem;
            color: #6c757d;
        }

        @media (min-width: 768px) {
            .md\:text-xs {
                font-size: 1rem; /* Tamaño de fuente más grande */
                line-height: 1.25rem; /* Aumentar el alto de la línea */
            }
            h1 {
                font-size: 3rem;
            }

            p {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    @if (session('mensaje'))
    <div class="alert bg-green-100 text-green-700 p-4 rounded-lg">
        {{ session('mensaje') }}
    </div>
@endif

    <div class="fullscreen-container">
        <div class="fullscreen-text text-center">
            <h1>📄 Confirmación de Presupuesto</h1>
            <p class="text-muted">Hola <strong>{{ $presupuesto->cliente_nombre }}</strong>,</p>
            <p>
                Este es el presupuesto para la reparación de tu bicicleta <strong class="text-success">{{ $presupuesto->bicicleta_nombre }}</strong>.
            </p>
            <p>Si aceptas, pasará al proceso de reparación lo antes posible. 🚴💨</p>

            <form action="{{ route('presupuesto.procesar', ['presupuestoId' => $presupuesto->id, 'token' => request('token')]) }}" method="POST">
                @csrf
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="submit" name="accion" value="aprobado" class="btn btn-success">
                        ✅ Aceptar Presupuesto
                    </button>
                    <button type="submit" name="accion" value="denegado" class="btn btn-danger">
                        ❌ Rechazar Presupuesto
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
