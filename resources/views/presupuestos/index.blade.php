@extends('layouts.app')

@section('content')
<!-- 🔹 Contenedor a pantalla completa -->
<div class="w-full px-4 sm:px-6 lg:px-10 mt-8">

    <h1 class="text-2xl font-bold text-center mb-4">Listado de Presupuestos</h1>

    @php
        $indexContext = request()->only(['page', 'search', 'origen', 'prioridad']);
    @endphp

    <!-- Formulario de Búsqueda -->
    <form method="GET" action="{{ route('presupuestos.index') }}" class="mb-4">
        <div class="flex justify-between">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Buscar presupuesto por cliente o bicicleta..."
                class="border px-4 py-2 rounded-md w-2/3">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Buscar
            </button>
        </div>
    </form>

    <!-- 🔹 Botones de Filtro -->
    <div class="mb-6 bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-3 items-center">
            <span class="font-semibold text-gray-700 mr-2">Filtros:</span>
            
            <!-- Filtro: Todos -->
            <a href="{{ route('presupuestos.index', ['search' => request('search')]) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ !request('origen') && !request('prioridad') ? 'bg-gray-800 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100' }}">
                <i class="bi bi-list-ul"></i> Todos
            </a>

            <!-- Filtro: Web -->
            <a href="{{ route('presupuestos.index', ['search' => request('search'), 'origen' => 'web', 'prioridad' => request('prioridad')]) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ request('origen') === 'web' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-300 hover:bg-blue-50' }}">
                <i class="bi bi-globe2"></i> Web
            </a>

            <!-- Filtro: Tienda -->
            <a href="{{ route('presupuestos.index', ['search' => request('search'), 'origen' => 'tienda', 'prioridad' => request('prioridad')]) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ request('origen') === 'tienda' ? 'bg-gray-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                <i class="bi bi-shop"></i> Tienda
            </a>

            <span class="text-gray-400 mx-2">|</span>

            <!-- Filtro: Premium -->
            <a href="{{ route('presupuestos.index', ['search' => request('search'), 'origen' => request('origen'), 'prioridad' => 'premium']) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ request('prioridad') === 'premium' ? 'bg-amber-400 text-black shadow-md border border-amber-600' : 'bg-white text-amber-600 border border-amber-300 hover:bg-amber-50' }}">
                <i class="bi bi-star-fill"></i> Premium
            </a>

            <!-- Filtro: Urgente -->
            <a href="{{ route('presupuestos.index', ['search' => request('search'), 'origen' => request('origen'), 'prioridad' => 'urgente']) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ request('prioridad') === 'urgente' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-red-600 border border-red-300 hover:bg-red-50' }}">
                <i class="bi bi-exclamation-triangle-fill"></i> Urgente
            </a>

            <!-- Filtro: Normal -->
            <a href="{{ route('presupuestos.index', ['search' => request('search'), 'origen' => request('origen'), 'prioridad' => 'normal']) }}" 
               class="px-4 py-2 rounded-md font-medium transition-all
                      {{ request('prioridad') === 'normal' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-blue-600 border border-blue-300 hover:bg-blue-50' }}">
                <i class="bi bi-clock"></i> Normal
            </a>

            @if(request('origen') || request('prioridad'))
                <a href="{{ route('presupuestos.index', ['search' => request('search')]) }}" 
                   class="ml-auto px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 border border-red-300 text-sm font-medium">
                    <i class="bi bi-x-circle"></i> Limpiar filtros
                </a>
            @endif
        </div>

        @if(request('origen') || request('prioridad'))
            <div class="mt-3 text-sm text-gray-600">
                <strong>Filtros activos:</strong>
                @if(request('origen'))
                    <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">
                        {{ request('origen') === 'web' ? '🌐 Web' : '🏪 Tienda' }}
                    </span>
                @endif
                @if(request('prioridad'))
                    <span class="inline-block bg-amber-100 text-amber-800 px-2 py-1 rounded ml-2">
                        {{ ucfirst(request('prioridad')) }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Mensajes de éxito -->
    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- 🔹 Tabla a ancho completo -->
    <div class="overflow-x-auto mt-6">
        <table class="w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-800 text-white text-sm">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Cliente</th>
                    <th class="py-2 px-4 text-left">Bicicleta</th>
                    <th class="py-2 px-4 text-left">Fecha</th>
                    <th class="py-2 px-4 text-left">Prioridad</th>
                    <th class="py-2 px-4 text-center">Origen</th>
                    <th class="py-2 px-4 text-center">Aprobar / Denegar</th>
                    <th class="py-2 px-4 text-center">Editar / Ver Presupuesto</th>
                    <th class="py-2 px-4 text-center">Eliminar / Enviar Correo</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300 text-sm">
                @foreach ($presupuestos as $presupuesto)
                    <tr class="hover:bg-gray-100 
                        {{ $presupuesto->estado === 'denegado' ? 'bg-red-200' : '' }}
                        {{ $presupuesto->estado === 'vacia' ? 'bg-yellow-200' : '' }}">
                        
                        <td class="py-2 px-4">
                            {{ $presupuesto->id }}<br>
                            <span class="text-gray-500 text-xs">{{ $presupuesto->idprograma }}</span>
                        </td>

                        <td class="py-2 px-4">{{ $presupuesto->user_nombre ?? 'N/A' }}</td>

                        <td class="py-2 px-4">
                            {{ $presupuesto->marca ?? 'N/A' }} {{ $presupuesto->bike_nombre ?? 'N/A' }}
                        </td>

                        <td class="py-2 px-4">
                            {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}
                        </td>

                        <td class="py-2 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                @if ($presupuesto->prioridad == 'urgente')
                                    bg-red-500 text-white
                                @elseif ($presupuesto->prioridad == 'premium')
                                    bg-amber-400 text-black border border-amber-600 shadow-sm premium
                                @else
                                    bg-blue-500 text-white
                                @endif">
                                {{ ucfirst($presupuesto->prioridad) }}
                                @if ($presupuesto->web && $presupuesto->prioridad == 'premium')
                                    <i class="bi bi-bell-fill text-orange-600" title="Premium desde Web"></i>
                                @endif
                            </span>
                        </td>

                        <!-- 🌐 Columna de Origen -->
                        <td class="py-2 px-4 text-center">
                            @if ($presupuesto->web)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">
                                    <i class="bi bi-globe2 mr-1"></i> Web
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                    <i class="bi bi-shop mr-1"></i> Tienda
                                </span>
                            @endif
                        </td>

                        <style>
                            .premium {
                                background: rgb(242, 255, 99);
                            }
                            .bi-bell-fill {
                                animation: ring 2s ease-in-out infinite;
                                display: inline-block;
                                margin-left: 4px;
                            }
                            @keyframes ring {
                                0%, 100% { transform: rotate(0deg); }
                                10%, 30% { transform: rotate(-10deg); }
                                20%, 40% { transform: rotate(10deg); }
                            }
                        </style>

                        <!-- 🟩 Aprobar / Denegar -->
                        <td class="py-2 px-4 text-center">
                            <form action="{{ route('presupuestos.actualizarEstado', $presupuesto->id) }}" method="POST" class="mb-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="aprobado">
                                <input type="hidden" name="return_page" value="{{ request('page') }}">
                                <input type="hidden" name="return_search" value="{{ request('search') }}">
                                <input type="hidden" name="return_origen" value="{{ request('origen') }}">
                                <input type="hidden" name="return_prioridad" value="{{ request('prioridad') }}">
                                <button type="submit" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 w-full">
                                    Aprobar
                                </button>
                            </form>

                            <form action="{{ route('presupuestos.actualizarEstado', $presupuesto->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="denegado">
                                <input type="hidden" name="return_page" value="{{ request('page') }}">
                                <input type="hidden" name="return_search" value="{{ request('search') }}">
                                <input type="hidden" name="return_origen" value="{{ request('origen') }}">
                                <input type="hidden" name="return_prioridad" value="{{ request('prioridad') }}">
                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 w-full">
                                    Denegar
                                </button>
                            </form>
                        </td>

                        <!-- 🟨 Editar / Ver Presupuesto -->
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col space-y-2">
                                          <a href="{{ route('presupuestos.edit', array_merge(['presupuesto' => $presupuesto->id], $indexContext)) }}"
                                   class="px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                   Editar
                                </a>                       

                                          <a href="{{ route('presupuestos.factura', array_merge(['id' => $presupuesto->id], $indexContext)) }}" 
                                   class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                   Ver Presupuesto
                                </a>

                                @if ($presupuesto->presupuesto_enviado)
                                    <p class="font-bold text-green-700 text-xs mt-2">SE HA ENVIADO EL MENSAJE</p>
                                @endif
                            </div>
                        </td>

                        <!-- 🟥 Eliminar / Enviar correo -->
                        <td class="py-2 px-4 text-center">
                            <div class="flex flex-col space-y-2">
                                <!-- Eliminar -->
                                <form action="{{ route('presupuestos.destroy', $presupuesto->id) }}" method="POST" 
                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este presupuesto?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_page" value="{{ request('page') }}">
                                    <input type="hidden" name="return_search" value="{{ request('search') }}">
                                    <input type="hidden" name="return_origen" value="{{ request('origen') }}">
                                    <input type="hidden" name="return_prioridad" value="{{ request('prioridad') }}">
                                    <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 w-full">
                                        Eliminar
                                    </button>
                                </form>

                                <!-- Enviar correo debajo -->
                                <a href="{{ url("/enviar/correo/presupuesto/{$presupuesto->id}") . (count(array_filter($indexContext, fn($v) => $v !== null && $v !== '')) ? ('?' . http_build_query(array_filter($indexContext, fn($v) => $v !== null && $v !== ''))) : '') }}" 
                                    class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                     Enviar correo
                                 </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $presupuestos->appends(request()->query())->links() }}
    </div>
</div>
@endsection
