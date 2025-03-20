@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">Factura del Presupuesto #{{ $presupuesto->id }}</h2>

        <!-- Información del Cliente -->
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Cliente ID:</strong> {{ $presupuesto->usuario_id }}</p>
                <p><strong>Nombre:</strong> {{ $presupuesto->usuario_nombre }}</p>
                <p><strong>Correo:</strong> {{ $presupuesto->usuario_email }}</p>                
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Bicicleta:</strong> {{ $presupuesto->bicicleta_nombre }}</p>
                <p><strong>Total Precio:</strong> <span class="badge bg-success fs-5">{{ number_format($presupuesto->precio_total, 2) }}€</span></p>
            </div>
        </div>

        <h3 class="mt-4 mb-3">Detalles:</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Componente</th>
                        <th>Minutos de Trabajo</th>
                        <th>Precio</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->componente_nombre }}</td>
                        <td>{{ $item->horas_trabajo }}</td>
                        <td class="text-success fw-bold">{{ number_format($item->total_precio, 2) }}€</td>
                        <td>{{ $item->texto }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Botones -->
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('presupuestos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('presupuestos.pdf', $presupuesto->id) }}" class="btn text-white" style="background-color: #E1251B;">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>            
        </div>
    </div>
</div>
@endsection
