@extends('layouts.app')
<style>
    .seat.selected .seat-body,
    .seat.selected .seat-base {
        fill: #1e90ff !important;
    }
</style>
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de pasajes</h5>
        </div>

        <div class="card-body">
            <div class="container mt-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            @foreach ($horarios as $horario)
                                <div class="col-md-6 mb-3">
                                    <div class="card horario-card" data-horario-id="{{ $horario->id }}">
                                        <div class="card-body">
                                            @php
                                                $capacidad = $horario->tipo_vehiculo->capacidad;
                                                $vendidos = $horario->pasajes_count;
                                                $disponibles = $capacidad - $vendidos;
                                            @endphp

                                            <h5 class="card-title">
                                                {{ $horario->tipo_vehiculo->descripcion }} –
                                                {{ $disponibles }} asientos disponibles
                                            </h5>

                                            <p class="card-text">
                                                {{ $horario->punto_origen->nombre_comercial }} →
                                                {{ $horario->punto_destino->nombre_comercial }} <br>
                                                {{ $horario->fecha_salida->format('d-m-Y') }} -
                                                {{ $horario->hora_embarque }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button id="sell-button" class="btn btn-primary mb-2" style="display:none;">
                            Vender pasaje
                        </button>

                        <div id="svg-container" style="max-width: 350px; overflow: auto;">
                            <p>Seleccione un horario para ver los asientos.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pasajes.js') }}"></script>
@endpush
