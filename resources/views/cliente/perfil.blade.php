@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">
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
                            <p class="card-text mb-1"><strong>Kilómetros:</strong> {{ $bike->kilometros ?? 'N/A' }}</p>
                            <p class="card-text mb-3"><strong>Color:</strong> {{ $bike->color ?? 'N/A' }}</p>
                        </div>

                        <div class="card-footer bg-white border-0 text-center">
                            <div class="btn-group-responsive">
                                <a href="{{ route('bikes.edit', $bike->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>

                                <a href="{{ route('cliente.cita', $bike->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-calendar-check"></i> Pedir Cita
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
/* 🔧 Contenedor flexible de botones */
.btn-group-responsive {
    display: flex;
    flex-wrap: nowrap; /* una fila en pantallas grandes */
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}

.btn-group-responsive .btn {
    flex: 1 1 0;
    min-width: 90px;
    white-space: nowrap;
    margin-bottom: 0.4rem; /* separación entre filas en móvil */
}

/* 📱 En pantallas pequeñas (hasta 480px, incluye iPhone 14 Pro Max) */
@media (max-width: 480px) {
    .btn-group-responsive {
        flex-wrap: wrap; /* permite varias filas */
        justify-content: center;
    }

    .btn-group-responsive .btn {
        flex: 0 0 45%; /* dos botones por fila */
    }
}

/* 💻 En pantallas grandes (a partir de 768px) */
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
