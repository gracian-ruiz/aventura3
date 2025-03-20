@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Detalles del Presupuesto</h1>

    <p><strong>ID:</strong> {{ $presupuesto->id }}</p>
    <p><strong>Cliente:</strong> {{ $presupuesto->user_nombre ?? 'N/A' }}</p>
    <p><strong>Bicicleta:</strong> {{ $presupuesto->bike_nombre ?? 'N/A' }}</p>
    <p><strong>Fecha de Creación:</strong> {{ date('d/m/Y', strtotime($presupuesto->created_at)) }}</p>

    <a href="{{ route('presupuestos.index') }}" class="mt-4 inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Volver</a>
</div>
@endsection
