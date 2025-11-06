@extends('layouts.app-cliente')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4">📅 Pedir Cita para {{ $bike->marca }} - {{ $bike->nombre }}</h2>

    {{-- ✅ Mensaje de éxito al crear la cita --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('cliente.guardarCita') }}">
        @csrf
        <input type="hidden" name="bike_id" value="{{ $bike->id }}">

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label for="fecha" class="form-label fw-bold">Selecciona una fecha</label>
                    <select name="fecha" id="fecha" class="form-select" required>
                        <option value="">-- Elige una fecha disponible --</option>
                        @foreach($fechasDisponibles as $fecha)
                            <option value="{{ $fecha }}">
                                {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l, d F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descripcion_problema" class="form-label fw-bold">Descripción del problema</label>
                    <textarea name="descripcion_problema"
                              id="descripcion_problema"
                              class="form-control"
                              rows="3"
                              placeholder="Describe brevemente el problema..."></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success px-5">Confirmar Cita</button>
                    <a href="{{ route('cliente.perfil') }}" class="btn btn-secondary px-4 ms-2">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
