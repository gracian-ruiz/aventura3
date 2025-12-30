@extends('layouts.app2')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">📦 Detalle del Alquiler <span class="text-muted">#{{ $alquiler->id }}</span></h1>

    <!-- Estado del alquiler -->
    <div class="mb-4">
        <h3>
            <strong>🔄 Estado del Alquiler:</strong>
            @switch($alquiler->estado)
                @case('reservado')
                    <span class="badge bg-warning text-dark fs-5">Reserva</span>
                    @break
                @case('activo')
                    <span class="badge bg-primary fs-5">Activo</span>
                    @break
                @case('finalizado')
                    <span class="badge bg-success fs-5">Finalizado</span>
                    @break
                @default
                    <span class="badge bg-secondary fs-5">Desconocido</span>
            @endswitch
        </h3>
    </div>

    <!-- Información general -->
    <div class="card shadow mb-5">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <strong>📅 Fecha de creación:</strong><br>
                    {{ $alquiler->created_at->format('d/m/Y') }}
                </div>
                <div class="col-md-2">
                    <strong>💰 Total precio (sin la reserva)</strong><br>
                    {{ number_format($alquiler->total_precio, 2) }} €
                </div>
                <div class="col-md-2">
                    <strong>🔻 Descuento total:</strong><br>
                    {{ number_format($alquiler->descuento, 2) }} €
                </div>
                <div class="col-md-2">
                    <strong>🔻 Precio Reserva:</strong><br>
                    {{ number_format($alquiler->reserva_precio, 2) }} €
                </div>
                <div class="col-md-2">
                    <strong>🔻 Precio total menos la reserva:</strong><br>
                    {{ number_format($alquiler->total_precio - $alquiler->reserva_precio, 2) }} €
                </div>
            </div>
        </div>
    </div>

    <!-- Observaciones -->
    <div class="col-md-12 mt-3 bg-danger text-white p-2 rounded">
        <strong>📝 Notas:</strong><br>
        @if($alquiler->observaciones)
            {{ $alquiler->observaciones }}
        @else
            <span class="text-light">Sin observaciones</span>
        @endif
    </div>

    @if($alquiler->fallo)
    <div class="col-md-12 mt-3 bg-warning p-2 rounded">
        <strong>⚠️ Incidencias:</strong><br>
        {{ $alquiler->incidencia }}
    </div>
    @endif

    <!-- Lista de materiales -->
    <h2 class="mt-5 mb-3">🔧 Materiales Alquilados</h2>

    @if($alquiler->materiales->isEmpty())
        <div class="alert alert-info">No hay materiales asociados a este alquiler.</div>
    @else
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Material</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Descuento</th>
                        <th>Reserva</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alquiler->materiales as $material)
                    <tr class="align-middle 
                        {{ $material->pivot->estado === 'finalizado' ? 'bg-red-100 text-red-700' : '' }} 
                        {{ $material->pivot->estado === 'activo' ? 'bg-green-100 text-green-700' : '' }}">
                        <td><strong>{{ $material->nombre }} - Talla {{ $material->talla }}</strong></td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($material->pivot->fecha_inicio)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($material->pivot->fecha_fin)->format('d/m/Y') }}</td>
                        <td class="text-end">{{ number_format($material->pivot->precio_unitario, 2) }} €</td>
                        <td class="text-end">{{ number_format($material->pivot->subtotal, 2) }} €</td>
                        <td class="text-end text-danger">{{ number_format($material->pivot->descuento, 2) }} €</td>
                        <td class="text-end text-primary">{{ number_format($material->pivot->reserva_precio, 2) }} €</td>
                        <td class="text-center">
                            @if($material->pivot->estado === 'finalizado')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Finalizado</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Activo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($material->pivot->estado !== 'finalizado')
                                <form id="devolverForm-{{ $material->pivot->id }}" action="{{ route('alquileres.material.devolver', $material->pivot->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" 
                                            class="btn btn-success btn-sm rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#devolverModal-{{ $material->pivot->id }}">
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

    <!-- Modales para devolución de cada material -->
    @foreach($alquiler->materiales as $material)
        <div class="modal fade" id="devolverModal-{{ $material->pivot->id }}" tabindex="-1" aria-labelledby="devolverModalLabel-{{ $material->pivot->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fs-4" id="devolverModalLabel-{{ $material->pivot->id }}">
                            <i class="bi bi-arrow-return-left me-2"></i>¿Devolver Material?
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fs-5 mb-3">¿Estás seguro de marcar este material como devuelto?</p>
                        <div class="alert alert-info mb-0">
                            <strong class="fs-5">{{ $material->nombre }} - Talla {{ $material->talla }}</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary fs-5" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-success fs-5" onclick="document.getElementById('devolverForm-{{ $material->pivot->id }}').submit()">
                            <i class="bi bi-check-circle-fill me-1"></i>Sí, Devolver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- 📸 Imágenes del DNI -->
    <h2 class="mt-5 mb-3">🪪 Imágenes del DNI del Cliente</h2>

    @if($alquiler->fotos && $alquiler->fotos->isNotEmpty())
        <div class="d-flex flex-wrap gap-3">
            @foreach($alquiler->fotos as $foto)
                <div class="border rounded shadow-sm p-2 text-center" style="width: 160px; cursor: pointer;">
                    <img 
                        src="{{ route('admin.dni.mostrar', $foto->id) }}" 
                        alt="Foto DNI" 
                        class="img-fluid rounded mb-2 zoomable-img" 
                        style="max-height: 120px; object-fit: cover;"
                        data-bs-toggle="modal"
                        data-bs-target="#imagenModal"
                        data-src="{{ route('admin.dni.mostrar', $foto->id) }}"
                    >
                    <div class="text-muted small">{{ ucfirst($foto->tipo ?? 'DNI') }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-secondary mt-3">
            No hay imágenes del DNI subidas para este cliente.
        </div>
    @endif

    <!-- 🧭 Modal para ampliar imagen -->
    <div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-center">
                <div class="modal-body p-0">
                    <img id="imagenAmpliada" src="" class="img-fluid rounded" alt="Imagen ampliada">
                </div>
                <button type="button" class="btn btn-light position-absolute top-0 end-0 m-2" data-bs-dismiss="modal">
                    ✖
                </button>
            </div>
        </div>
    </div>

    <!-- Botón de cambio de estado -->
    @if($alquiler->estado !== 'finalizado')
        <form id="estadoForm" action="{{ route('alquileres.finalizar', $alquiler->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="nuevo" value="{{ $alquiler->estado === 'reservado' ? 'activo' : 'finalizado' }}">
            <button type="button" 
                    class="btn {{ $alquiler->estado === 'reservado' ? 'btn-success' : 'btn-danger' }}"
                    data-bs-toggle="modal" 
                    data-bs-target="#{{ $alquiler->estado === 'reservado' ? 'alquilarModal' : 'finalizarModal' }}">
                <i class="bi {{ $alquiler->estado === 'reservado' ? 'bi-check-circle-fill' : 'bi-archive-fill' }}"></i>
                {{ $alquiler->estado === 'reservado' ? 'Alquilar' : 'Finalizar Alquiler' }}
            </button>
        </form>
    @endif

    <!-- Modal para confirmar ALQUILAR -->
    <div class="modal fade" id="alquilarModal" tabindex="-1" aria-labelledby="alquilarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fs-4" id="alquilarModalLabel">
                        <i class="bi bi-check-circle-fill me-2"></i>¿Confirmar Alquiler?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fs-5 mb-0">¿Estás seguro de activar este alquiler? Se marcarán todos los materiales como <strong>activos</strong>.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary fs-5" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success fs-5" onclick="document.getElementById('estadoForm').submit()">
                        <i class="bi bi-check-circle-fill me-1"></i>Sí, Alquilar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar FINALIZAR -->
    <div class="modal fade" id="finalizarModal" tabindex="-1" aria-labelledby="finalizarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fs-4" id="finalizarModalLabel">
                        <i class="bi bi-archive-fill me-2"></i>¿Finalizar Alquiler?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fs-5 mb-0">¿Estás seguro de finalizar este alquiler? Se marcarán todos los materiales como <strong>finalizados</strong>.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary fs-5" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger fs-5" onclick="document.getElementById('estadoForm').submit()">
                        <i class="bi bi-archive-fill me-1"></i>Sí, Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de volver -->
    <div class="mt-4">
        <a href="{{ route('alquileres.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>

<!-- 🔍 Script para cambiar imagen dentro del modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('imagenModal');
    const modalImg = document.getElementById('imagenAmpliada');

    modal.addEventListener('show.bs.modal', function (event) {
        const img = event.relatedTarget;
        modalImg.src = img.getAttribute('data-src');
    });

    // Cierra el modal si se hace clic fuera de la imagen
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();
        }
    });
});
</script>
@endsection
