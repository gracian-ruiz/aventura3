@extends('layouts.app-cliente')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4">📋 Presupuesto de Reparación</h2>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="card-title text-primary">Bicicleta: {{ $appointment->bike->marca }} - {{ $appointment->bike->nombre }}</h5>
            <p class="mb-1"><strong>Fecha de cita:</strong> {{ $appointment->calendario ? \Carbon\Carbon::parse($appointment->calendario)->format('d/m/Y') : 'Sin fecha' }}</p>
            <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-warning">{{ ucfirst($appointment->estado) }}</span></p>
            
            @if($appointment->descripcion_cliente)
                <p class="mb-0"><strong>Descripción del problema:</strong> {{ $appointment->descripcion_cliente }}</p>
            @endif
        </div>
    </div>

    @if($data->isEmpty())
        <div class="alert alert-info text-center">
            El presupuesto aún no ha sido preparado por el taller. Por favor, espera a que revisen tu bicicleta.
        </div>
    @else
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalles del Presupuesto</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Componente</th>
                                <th>Descripción</th>
                                <th class="text-center">Tiempo (min)</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Descuento (%)</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalGeneral = 0;
                            @endphp
                            @foreach($data as $item)
                                @php
                                    $manoObra = (float) ($item->total_precio ?? 0);
                                    $material = (float) ($item->precio_material ?? 0);
                                    $precioBruto = $manoObra + $material;
                                    $descuentoPct = (float) ($item->descuento ?? 0);
                                    $descuentoImporte = round($precioBruto * ($descuentoPct / 100), 2);
                                    $precioFinal = max($precioBruto - $descuentoImporte, 0);
                                    $totalGeneral += $precioFinal;
                                @endphp
                                <tr>
                                    <td><strong>{{ $item->componente_nombre }}</strong></td>
                                    <td>{{ $item->texto ?? 'Sin descripción' }}</td>
                                    <td class="text-center">{{ $item->horas_trabajo }} min</td>
                                    <td class="text-end">{{ number_format($precioBruto, 2) }}€</td>
                                    <td class="text-end text-danger">
                                        @if($descuentoPct > 0)
                                            {{ number_format($descuentoPct, 2) }}% (-{{ number_format($descuentoImporte, 2) }}€)
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end"><strong>{{ number_format($precioFinal, 2) }}€</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total Presupuesto:</strong></td>
                                <td class="text-end"><strong class="text-success fs-5">{{ number_format($totalGeneral, 2) }}€</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-warning mt-3">
                    <strong>⚠️ Importante:</strong> Al aprobar el presupuesto, tu bicicleta entrará en la cola de reparación. 
                    Si lo denegas, la cita será cancelada.
                </div>

                <div class="text-center mb-3">
                    <a href="{{ route('cliente.descargarPresupuestoPDF', $appointment->id) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Descargar Presupuesto en PDF
                    </a>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4 w-100">
                    <button type="button" class="btn btn-success btn-lg flex-fill mb-2 mb-md-0 px-4" data-bs-toggle="modal" data-bs-target="#aprobarModal">
                        <i class="bi bi-check-circle"></i> Aprobar Presupuesto
                    </button>

                    <button type="button" class="btn btn-danger btn-lg flex-fill px-4" data-bs-toggle="modal" data-bs-target="#denegarModal">
                        <i class="bi bi-x-circle"></i> Denegar Presupuesto
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('cliente.perfil') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Mis Bicicletas
        </a>
    </div>
</div>

<!-- Modal de Confirmación para Aprobar -->
<div class="modal fade" id="aprobarModal" tabindex="-1" aria-labelledby="aprobarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="aprobarModalLabel">
                    <i class="bi bi-check-circle"></i> Aprobar Presupuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Estás seguro de que quieres aprobar este presupuesto?</p>
                <div class="alert alert-info mb-0">
                    <strong>📌 Nota:</strong> Al aprobar, tu bicicleta entrará en la cola de reparación y comenzará el proceso.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('cliente.aprobarPresupuesto', $appointment->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Sí, Aprobar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Denegar -->
<div class="modal fade" id="denegarModal" tabindex="-1" aria-labelledby="denegarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="denegarModalLabel">
                    <i class="bi bi-x-circle"></i> Denegar Presupuesto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Estás seguro de que quieres denegar este presupuesto?</p>
                <div class="alert alert-warning mb-0">
                    <strong>⚠️ Atención:</strong> Al denegar el presupuesto, la cita será cancelada y deberás solicitar una nueva si deseas reparar tu bicicleta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('cliente.denegarPresupuesto', $appointment->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Sí, Denegar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 600px) {
    .table-responsive table {
        font-size: 0.95rem;
    }
    .btn-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .d-flex.flex-md-row {
        flex-direction: column !important;
    }
    .d-flex.flex-md-row .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

@endsection
