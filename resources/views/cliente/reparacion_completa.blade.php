@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-4">
        @if($appointment->estado === 'en proceso')
            🔧 Reparación en Curso #{{ $appointment->id }}
        @elseif($appointment->estado === 'pendiente')
            ⏳ Reparación Pendiente #{{ $appointment->id }}
        @else
            🧾 Detalle de Reparación #{{ $appointment->id }}
        @endif
    </h1>

    {{-- Banner de estado --}}
    @if($appointment->estado === 'en proceso')
        <div class="alert alert-primary text-center mb-4">
            <i class="bi bi-tools fs-4"></i>
            <h5 class="mt-2 mb-0">Tu bicicleta está siendo reparada en este momento</h5>
            <small>A continuación puedes ver el progreso de la reparación</small>
        </div>
    @elseif($appointment->estado === 'pendiente')
        <div class="alert alert-warning text-center mb-4">
            <i class="bi bi-hourglass-split fs-4"></i>
            <h5 class="mt-2 mb-0">Tu presupuesto ha sido aprobado</h5>
            <small>La reparación comenzará próximamente</small>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $appointment->bike->marca }} - {{ $appointment->bike->nombre }}</h5>
            <p><strong>Fecha Asignada:</strong> {{ $appointment->fecha_asignada ? \Carbon\Carbon::parse($appointment->fecha_asignada)->format('d/m/Y') : 'Por asignar' }}</p>
            <p><strong>Prioridad:</strong> 
                <span class="badge 
                    @if($appointment->prioridad === 'premium') bg-dark
                    @elseif($appointment->prioridad === 'urgente') bg-danger
                    @else bg-primary
                    @endif">
                    {{ ucfirst($appointment->prioridad) }}
                </span>
            </p>
            <p><strong>Estado:</strong> 
                <span class="badge 
                    @if($appointment->estado === 'en proceso') bg-primary
                    @elseif($appointment->estado === 'pendiente') bg-warning text-dark
                    @else bg-success
                    @endif">
                    {{ ucfirst($appointment->estado) }}
                </span>
            </p>
            @if($appointment->descripcion_problema || $appointment->descripcion_cliente)
                <p><strong>Descripción del Problema:</strong> {{ $appointment->descripcion_problema ?? $appointment->descripcion_cliente ?? '—' }}</p>
            @endif
            <p><strong>Tiempo Estimado:</strong> {{ $appointment->horas_total }} min</p>
            <p><strong>Precio Total:</strong> <span class="text-success fs-5">{{ number_format($appointment->precio_total, 2) }} €</span></p>
        </div>
    </div>

    <h4 class="mb-3">🧩 Trabajos a Realizar</h4>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>Componente</th>
                    <th>Texto</th>
                    <th>Horas</th>
                    <th>Precio (€)</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item->component_nombre }}</td>
                        <td>{{ $item->texto ?? '—' }}</td>
                        <td>{{ $item->horas_trabajo }}</td>
                        <td>{{ number_format($item->total_precio, 2) }}</td>
                        <td>
                            @if($item->checked)
                                <span class="badge bg-success">Completado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="fw-bold table-secondary">
                <tr>
                    <td colspan="2" class="text-end">Totales:</td>
                    <td>{{ $data->sum('horas_trabajo') }}</td>
                    <td>{{ number_format($data->sum('total_precio'), 2) }} €</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('cliente.perfil') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Perfil
        </a>
    </div>
</div>
@endsection
