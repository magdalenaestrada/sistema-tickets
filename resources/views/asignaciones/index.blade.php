@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4>Asignaciones de Horarios</h4>
                <button class="btn btn-primary" id="btnNuevo">Nueva Asignación</button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <table class="table table-striped table-hover" id="tablaAsignaciones">
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Primer Conductor</th>
                            <th>Segundo Conductor</th>
                            <th>Vehículo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    @include('asignaciones.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/asignaciones.js') }}"></script>
@endpush
