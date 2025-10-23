@extends('layouts.app-cliente')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-5">🔧 Revisiones de {{ $bike->marca }} - {{ $bike->nombre }}</h1>

    @if($revisiones->isEmpty())
        <div class="alert alert-info text-center">
            Esta bicicleta aún no tiene revisiones registradas.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha de Revisión</th>
                        <th>Descripción</th>
                        <th>Próxima Revisión</th>
                        <th>Componente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revisiones as $revision)
                        <tr>
                            <td>{{ $revision->fecha_revision }}</td>
                            <td>{{ $revision->descripcion }}</td>
                            <td>{{ $revision->proxima_revision ?? '—' }}</td>
                            <td>{{ optional($revision->componente)->nombre ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('cliente.perfil') }}" class="btn btn-secondary">
            ← Volver a Mis Bicicletas
        </a>
    </div>
</div>
@endsection
