@extends('layouts.app2')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">📦 Detalle del Alquiler <span class="text-muted">#{{ $alquiler->id }}</span></h1>

    <!-- Estado del alquiler -->
    <div class="mb-4">
        <h5>
            <strong>🔄 Estado del Alquiler:</strong>
            @switch($alquiler->estado)
                @case('reservado')
                    <span class="badge bg-warning text-dark">Reserva</span>
                    @break
                @case('activo')
                    <span class="badge bg-primary">Activo</span>
                    @break
                @case('finalizado')
                    <span class="badge bg-success">Finalizado</span>
                    @break
                @default
                    <span class="badge bg-secondary">Desconocido</span>
            @endswitch
        </h5>
    </div>

    <!-- Información general del alquiler -->
    <div class="card shadow mb-5">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>📅 Fecha de creación:</strong><br>
                    {{ $alquiler->created_at->format('d/m/Y') }}
                </div>
                <div class="col-md-4">
                    <strong>💰 Total precio:</strong><br>
                    {{ number_format($alquiler->total_precio, 2) }} €
                </div>
                <div class="col-md-4">
                    <strong>🔻 Descuento total:</strong><br>
                    {{ number_format($alquiler->descuento, 2) }} €
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de materiales -->
    <h2 class="mb-3">🔧 Materiales Alquilados</h2>

    @if($alquiler->materiales->isEmpty())
        <div class="alert alert-info">No hay materiales asociados a este alquiler.</div>
    @else
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alquiler->materiales as $material)
                    <tr class="align-middle 
                        {{ $material->pivot->estado === 'finalizado' ? 'bg-red-100 text-red-700' : '' }} 
                        {{ $material->pivot->estado === 'activo' ? 'bg-green-100 text-green-700' : '' }}">
                        <td class="px-3 py-3"><strong>{{ $material->nombre }}</strong></td>
                        <td class="text-center px-3 py-3">{{ $material->pivot->cantidad }}</td>
                        <td class="text-center px-3 py-3">{{ \Carbon\Carbon::parse($material->pivot->fecha_inicio)->format('d/m/Y') }}</td>
                        <td class="text-center px-3 py-3">{{ \Carbon\Carbon::parse($material->pivot->fecha_fin)->format('d/m/Y') }}</td>
                        <td class="text-end px-3 py-3">{{ number_format($material->pivot->precio_unitario, 2) }} €</td>
                        <td class="text-end px-3 py-3">{{ number_format($material->pivot->subtotal, 2) }} €</td>
                        <td class="text-end px-3 py-3 text-danger fw-semibold">{{ number_format($material->pivot->descuento, 2) }} €</td>
                        <td class="text-center px-3 py-3">
                            @if($material->pivot->estado === 'finalizado')
                                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Finalizado</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-clock me-1"></i> Activo</span>
                            @endif
                        </td>
                        <td class="text-center px-3 py-3">
                            @if($material->pivot->estado !== 'finalizado')
                                <form action="{{ route('alquileres.material.devolver', $material->pivot->id) }}" method="POST" onsubmit="return confirm('¿Marcar este material como devuelto?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill shadow-sm">
                                        <i class="bi bi-arrow-return-left me-1"></i> Devolver
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Devuelto
                                </button>
                            @endif
                        </td>
                    </tr>                                    
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Botón de cambio de estado -->
    @if($alquiler->estado !== 'finalizado')
        <form action="{{ route('alquileres.finalizar', $alquiler->id) }}" method="POST" class="mt-4"
              onsubmit="return confirm('{{ $alquiler->estado === 'reserva' ? '¿Confirmar alquiler y activar materiales?' : '¿Finalizar el alquiler y todos los materiales?' }}')">
            @csrf
            @method('PATCH')
            <input type="hidden" name="nuevo" value="{{ $alquiler->estado === 'reservado' ? 'activo' : 'finalizado' }}">
            <button type="submit" class="btn {{ $alquiler->estado === 'reservado' ? 'btn-success' : 'btn-danger' }}">
                <i class="bi {{ $alquiler->estado === 'reservado' ? 'bi-check-circle-fill' : 'bi-archive-fill' }}"></i>
                {{ $alquiler->estado === 'reservado' ? 'Alquilar' : 'Finalizar Alquiler' }}
            </button>
        </form>
    @endif

    <!-- Botón de volver -->
    <div class="mt-4">
        <a href="{{ route('alquileres.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
@endsection
