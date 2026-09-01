@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-5">🔧 Historial de {{ $bike->marca }} - {{ $bike->nombre }}</h1>

    @if($historial->isEmpty())
        <div class="alert alert-info text-center">
            Esta bicicleta no tiene reparaciones registradas.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha Asignada</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Descripción</th>
                        <th>Ver Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historial as $cita)
                        <tr>
                            <td>#{{ $cita->id }}</td>
                            <td>{{ $cita->fecha_asignada ?? '—' }}</td>
                            <td>{{ ucfirst($cita->prioridad) }}</td>
                            <td>
                                <span class="app-badge {{ $cita->estado === 'completada' ? 'app-badge-status-completada' : ($cita->estado === 'en proceso' ? 'app-badge-status-proceso' : 'app-badge-status-pendiente') }}">
                                    {{ ucfirst($cita->estado) }}
                                </span>
                            </td>
                            <td>{{ $cita->descripcion_problema ?? '—' }}</td>
                            <td>
                                <a href="{{ route('cliente.reparacion_completa', $cita->id) }}" class="btn btn-outline-primary btn-sm">
                                    Ver Reparación
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('cliente.perfil') }}" class="btn btn-secondary">← Volver a Mis Bicicletas</a>
    </div>
</div>
@endsection
