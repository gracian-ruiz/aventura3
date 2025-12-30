@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">

    {{-- ✅ Mensaje de éxito si la cita se creó correctamente --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <h1 class="text-center mb-5">🚴 Mis Bicicletas</h1>

    @if($bikes->isEmpty())
        <div class="alert alert-info text-center">
            Aún no tienes bicicletas registradas.
        </div>
    @else
        <div class="row justify-content-center">
            @foreach($bikes as $bike)
                <div class="col-sm-10 col-md-8 col-lg-6 mb-4 d-flex justify-content-center">
                    <div class="card h-100 shadow border-0" style="width: 28rem; max-width: 100%;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary mb-3">{{ $bike->marca }} - {{ $bike->nombre }}</h5>
                            <p class="card-text mb-1"><strong>Año:</strong> {{ $bike->anio_modelo }}</p>
                            <p class="card-text mb-1"><strong>Kilómetros:</strong> {{ $bike->kilometros ?? 'N/D' }}</p>
                            <p class="card-text mb-3"><strong>Color:</strong> {{ $bike->color ?? 'N/D' }}</p>

                            {{-- 📋 Mostrar todas las citas según su estado --}}
                            @if($bike->appointments->isNotEmpty())
                                @foreach($bike->appointments as $cita)
                                    @php
                                        // 🎨 Definir colores y mensajes según el estado
                                        $alertClass = 'alert-primary';
                                        $iconClass = 'bi-clock-history';
                                        $mensaje = 'Pendiente de revisión o presupuesto en tienda';
                                        
                                        switch($cita->estado) {
                                            case 'presupuesto':
                                                $alertClass = 'alert-info';
                                                $iconClass = 'bi-file-earmark-text';
                                                $mensaje = '📋 Pendiente de revisión o presupuesto en tienda';
                                                break;
                                            case 'pendiente':
                                                $alertClass = 'alert-warning';
                                                $iconClass = 'bi-hourglass-split';
                                                $mensaje = '⏳ Presupuesto aceptado - Pendiente de reparación';
                                                break;
                                            case 'en proceso':
                                                $alertClass = 'alert-primary';
                                                $iconClass = 'bi-tools';
                                                $mensaje = '🔧 Bicicleta en reparación';
                                                break;
                                            case 'completada':
                                                $alertClass = 'alert-success';
                                                $iconClass = 'bi-check-circle';
                                                $mensaje = '✅ Reparación completada - Lista para recoger';
                                                break;
                                            case 'denegado':
                                                $alertClass = 'alert-danger';
                                                $iconClass = 'bi-x-circle';
                                                $mensaje = '❌ Presupuesto denegado';
                                                break;
                                        }
                                    @endphp
                                    
                                    <div class="alert {{ $alertClass }} py-2 mt-2 mb-0">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi {{ $iconClass }} me-2"></i>
                                            <strong>{{ $mensaje }}</strong>
                                        </div>
                                        
                                        @if(!empty($cita->calendario))
                                            <div class="small">
                                                📅 Fecha: <strong>{{ \Carbon\Carbon::parse($cita->calendario)->locale('es')->translatedFormat('l d \d\e F \d\e Y') }}</strong>
                                            </div>
                                        @endif
                                        
                                        @if($cita->descripcion_cliente)
                                            <div class="mt-2 text-start small">
                                                <strong>Problema:</strong> {{ Str::limit($cita->descripcion_cliente, 80) }}
                                            </div>
                                        @endif
                                        
                                        {{-- Mostrar botón según el estado --}}
                                        @if($cita->estado === 'presupuesto')
                                            <div class="mt-2">
                                                <a href="{{ route('cliente.presupuesto', $cita->id) }}" class="btn btn-info btn-sm">
                                                    <i class="bi bi-file-earmark-text"></i> Ver Presupuesto
                                                </a>
                                            </div>
                                        @endif
                                        
                                        @if($cita->estado === 'en proceso' || $cita->estado === 'pendiente')
                                            <div class="mt-2">
                                                <a href="{{ route('cliente.reparacion_completa', $cita->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-tools"></i> Ver Reparación
                                                </a>
                                            </div>
                                        @endif
                                        
                                        {{-- Mensaje especial cuando está completada --}}
                                        @if($cita->estado === 'completada')
                                            <div class="mt-2 alert alert-light mb-0 py-1">
                                                <small><i class="bi bi-info-circle"></i> Puedes pasar a recoger tu bicicleta en nuestro horario habitual</small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted small mt-3">No hay citas activas para esta bicicleta.</div>
                            @endif
                        </div>

                        <div class="card-footer bg-white border-0 text-center">
                            <div class="btn-group-responsive">
                                <a href="{{ route('cliente.cita', $bike->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-calendar-check"></i> Pedir cita
                                </a>

                                <a href="{{ route('cliente.revisiones', $bike->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-wrench"></i> Revisiones
                                </a>

                                <a href="{{ route('cliente.historial', $bike->id) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-clock-history"></i> Historial
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.btn-group-responsive {
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}
.btn-group-responsive .btn {
    flex: 1 1 0;
    min-width: 90px;
    white-space: nowrap;
    margin-bottom: 0.4rem;
}
@media (max-width: 480px) {
    .btn-group-responsive {
        flex-wrap: wrap;
        justify-content: center;
    }
    .btn-group-responsive .btn {
        flex: 0 0 45%;
    }
}
@media (min-width: 768px) {
    .btn-group-responsive {
        flex-wrap: nowrap;
    }
    .btn-group-responsive .btn {
        flex: 1;
        margin-bottom: 0;
    }
}
</style>
@endsection
