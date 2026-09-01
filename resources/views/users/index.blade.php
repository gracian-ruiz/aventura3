@extends('layouts.app')

@section('content')
<div class="w-full mt-8 px-4 sm:px-6 lg:px-10">
    <h1 class="app-title text-center mb-4">Gestión de Usuarios</h1>
    
    <!-- Formulario de Búsqueda -->
    <div class="app-toolbar">
        <form method="GET" action="{{ route('users.index') }}" class="mb-2">
            <div class="app-search-row">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Buscar usuario por nombre o email..."
                    class="app-search-input sm:flex-1">

                <button type="submit" class="app-btn app-btn-primary">
                    Buscar
                </button>
            </div>
        </form>
        <div class="text-sm text-slate-600">
            Mostrando {{ $users->count() }} de {{ $users->total() }} usuarios
        </div>
    </div>

    <!-- Botón para Añadir Nuevo Usuario (Solo Admins) -->
    @if(Auth::user()->role === 'admin')
        <div class="flex justify-end">
            <a href="{{ route('users.create') }}" 
               class="px-4 py-2 bg-green-500 text-white rounded-md shadow-md hover:bg-green-600 transition duration-200">
                + Nuevo Usuario
            </a>
        </div>
    @endif

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabla de Usuarios -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-md rounded-lg table-mobile-friendly">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Nombre</th>
                    <th class="py-2 px-4 text-left">Email</th>
                    <th class="py-2 px-4 text-left">Teléfono</th>
                    <th class="py-2 px-4 text-left">Bicicletas</th>
                    <th class="py-2 px-4 text-left">Presupuesto</th>
                    @if(Auth::user()->role === 'admin')
                        <th class="py-2 px-4 text-center">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4">{{ $user->id }}</td>
                        <td class="py-2 px-4">{{ $user->name }}</td>
                        <td class="py-2 px-4">{{ $user->email }}</td>
                        <td class="py-2 px-4">{{ $user->telefono }}</td>
                        <td class="py-2 px-4">
                            <a href="{{ route('users.bikes', $user->id) }}" 
                               class="inline-flex items-center px-3 py-2 {{ $user->bikes->isEmpty() ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-500 hover:bg-blue-600' }} text-white rounded-md transition">
                                <i class="bi bi-bicycle mr-2"></i>
                                @if($user->bikes->isEmpty())
                                    Añadir Bicicleta
                                @else
                                    Ver Bicicletas ({{ $user->bikes->count() }})
                                @endif
                            </a>
                        </td>
                        
                        <td>
                            <a href="{{ route('presupuestos.create', $user->id) }}" class="app-btn app-btn-success">
                                Crear Presupuesto
                            </a>
                        </td>
                        
                        @if(Auth::user()->role === 'admin')
                            <td class="py-2 px-4 text-center">
                                <a href="{{ route('users.edit', $user->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Editar</a>

                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600" onclick="return confirm('¿Seguro que quieres eliminar este usuario?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $users->appends(['search' => request('search')])->links() }}
    </div>

</div>
@endsection
