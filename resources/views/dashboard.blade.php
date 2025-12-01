@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <h4>Hola, {{ $usuario->persona->nombres ?? $usuario->name }}</h4>
                <p>Sucursal: {{ $usuario->sucursal->nombre_comercial ?? 'No asignada' }}</p>
            </div>
        </div>

        <!-- Tarjetas métricas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Ventas del dia</h5>
                        <p class="card-text fs-4">{{ number_format($montoActual, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de horarios disponibles hoy -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Horarios disponibles hoy
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo de viaje</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Vehículo</th>
                                    <th>Hora embarque</th>
                                    <th>Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($horariosHoy as $horario)
                                    <tr>
                                        <td>{{ $horario->tipo_viaje->descripcion ?? '-' }}</td>
                                        <td>{{ $horario->punto_origen->nombre_comercial ?? '-' }}</td>
                                        <td>{{ $horario->punto_destino->nombre_comercial ?? '-' }}</td>
                                        <td>{{ $horario->tipo_vehiculo->descripcion ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($horario->hora_embarque)->format('H:i') }}</td>
                                        <td>{{ number_format($horario->costo_pasaje, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
