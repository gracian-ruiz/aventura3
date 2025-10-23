@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-4">🧾 Detalle de Reparación #{{ $appointment->id }}</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $appointment->bike->marca }} - {{ $appointment->bike->nombre }}</h5>
            <p><strong>Fecha Asignada:</strong> {{ $appointment->fecha_asignada }}</p>
            <p><strong>Prioridad:</strong> {{ ucfirst($appointment->prioridad) }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($appointment->estado) }}</p>
            <p><strong>Descripción del Problema:</strong> {{ $appointment->descripcion_problema ?? '—' }}</p>
            <p><strong>Horas Totales:</strong> {{ $appointment->horas_total }} h</p>
            <p><strong>Precio Total:</strong> {{ number_format($appointment->precio_total, 2) }} €</p>
        </div>
    </div>

    <h4 class="mb-3">🧩 Componentes Usados</h4>

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
        <a href="{{ route('cliente.historial', $appointment->bike_id) }}" class="btn btn-secondary">
            ← Volver al Historial
        </a>
    </div>
</div>
@endsection
