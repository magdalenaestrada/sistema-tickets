@extends('layouts.app')

@section('content')
<div class="container">

    <h4>Asignar Encomiendas</h4>
    <hr>

    <div class="card mb-3">
        <div class="card-body">
            <strong>Asignación:</strong> #{{ $asignacion->id }} <br>
            <strong>Conductor:</strong> {{ $asignacion->primerConductor->nombres }} <br>
            <strong>Vehículo:</strong> {{ $asignacion->vehiculo->placa ?? 'N/A' }}
        </div>
    </div>

    <div class="mb-3">
        <input id="inputBuscar" class="form-control" placeholder="Buscar encomienda...">
    </div>

    <form id="formAsignacion">
        @csrf

        <input type="hidden" name="asignacion_id" value="{{ $asignacion->id }}">

        <table class="table table-bordered" id="tablaEncomiendas">
            <thead>
                <tr>
                    <th>Sel</th>
                    <th>ID</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Fecha</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">
                Asignar seleccionadas
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/asignaciones_encomiendas.js') }}"></script>
@endpush
