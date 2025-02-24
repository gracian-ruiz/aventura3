<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taller Aventura Bike</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-100">
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900">Iniciar sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900">Registrarse</a>
                    @endif
                @endauth
            </div>
        @endif

        <!-- Contenedor general -->
        <div class="w-full flex flex-col items-center">
            <!-- Logo del taller -->
            <div class="mt-6">
                <img src="/images/logo_taller.jpeg" alt="Logo Taller" class="h-24 w-auto">
            </div>

            <!-- Imagen de fondo con enlace -->
            <a href="{{ auth()->check() ? url('/dashboard') : route('login') }}" 
               class="w-full max-w-7xl h-[500px] sm:h-[600px] lg:h-[700px] bg-cover bg-center shadow-lg transition-transform hover:scale-105"
               style="background-image: url('/images/taller-fondo.jpg');">
            </a>

            <!-- Pie de página -->
            <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between w-full text-center text-sm text-gray-500">
                Taller Aventura Bike - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </div>
        </div>
    </div>
</body>
</html>
