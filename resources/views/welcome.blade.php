@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <!-- Bienvenida -->
        <div class="col-12">
            <h4>Hola, {{ auth()->user()->name }}</h4>
            <p>Sucursal: {{ auth()->user()->sucursal->nombre ?? 'No asignada' }}</p>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Tarjetas de métricas -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Ventas del día</h5>
                    <p class="card-text fs-4">{{ $ventasHoy }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Encomiendas recibidas</h5>
                    <p class="card-text fs-4">{{ $encomiendasHoy }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Horarios disponibles hoy</h5>
                    <p class="card-text fs-4">{{ $horariosHoy->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de horarios -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    Horarios disponibfffles hoy
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
                            @foreach($horariosHoy as $horario)
                                <tr>
                                    <td>{{ $horario->tipo_viaje->nombre ?? '-' }}</td>
                                    <td>{{ $horario->punto_origen->nombre ?? '-' }}</td>
                                    <td>{{ $horario->punto_destino->nombre ?? '-' }}</td>
                                    <td>{{ $horario->tipo_vehiculo->nombre ?? '-' }}</td>
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
