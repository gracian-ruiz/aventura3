<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en la Confirmación</title>
    <link rel="stylesheet" href="path/to/tailwind.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8d7da;
            color: #721c24;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .error-container {
            text-align: center;
            background-color: #f8d7da;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            border: 1px solid #f5c6cb;
        }

        h1 {
            font-size: 2rem;
            color: #721c24;
            font-weight: bold;
        }

        p {
            font-size: 1.2rem;
        }

        .btn {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="error-container">
        <h1>⚠️ Error en la Confirmación</h1>
        <p>{{ $mensaje }}</p>
       <p>Llamanos o envia un whassapp a este numero 699 99 99 99</p>
    </div>

</body>
</html>
