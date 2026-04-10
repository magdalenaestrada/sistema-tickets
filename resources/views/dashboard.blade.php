@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h4>Hola, {{ $usuario->persona->nombres ?? $usuario->name }}</h4>
                <p>Sucursal: {{ $usuario->sucursal->nombre_comercial ?? 'No asignada' }}</p>
            </div>
        </div>

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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Horarios disponibles hoy
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
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

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
