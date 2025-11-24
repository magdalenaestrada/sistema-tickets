@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Horarios</h5>
            <div class="d-flex gap-2"> <button class="btn btn-primary" id="btnNuevoHorario"> <i class="link-icon"
                        data-lucide="plus"></i>
                    Añadir Horario </button> </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaHorarios" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de viaje</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Vehículo</th>
                            <th>Costo</th>
                            <th>Hora embarque</th>
                            <th>Fecha salida</th>
                            <th>Días</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('horarios.modals.create')
    @include('horarios.modals.puntos')
@endsection

@push('scripts')
    <script src="{{ asset('js/horarios.js') }}"></script>
@endpush
