<h3>Datos del cliente:</h3>
<ul>
    <li><strong>Nombre:</strong> {{ $usuario->nombre }}</li>
    <li><strong>Email:</strong> {{ $usuario->email }}</li>
    <li><strong>Teléfono:</strong> {{ $usuario->telefono }}</li>
    <li><strong>DNI:</strong> {{ $usuario->dni }}</li>
    @if($usuario->direccion)
        <li><strong>Dirección:</strong> {{ $usuario->direccion }}</li>
    @endif
    @if(!empty($observaciones))
    <h3>Observaciones:</h3>
    <p>{{ $observaciones }}</p>
@endif
</ul>

<h3>Resumen de tu solicitud:</h3>
<ul>
    @foreach($bicicletas as $bici)
        <li>
            {{ $bici['cantidad'] }} bicicleta(s) tipo bicicleta <strong>{{ $bici['tipo_bonito'] }}</strong>,
            talla <strong>{{ $bici['talla'] }}</strong>,
            del {{ \Carbon\Carbon::parse($bici['fecha_inicio'])->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($bici['fecha_fin'])->format('d/m/Y') }}
        </li>
    @endforeach
</ul>

