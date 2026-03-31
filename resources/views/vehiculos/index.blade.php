@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de vehiculos</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevaVehiculo">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Vehiculo
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaVehiculos" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de vehiculo</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Hab. vehicular</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('vehiculos.modals.create')
    @include('vehiculos.modals.mantenimiento')
@endsection

@push('scripts')
    <script src="{{ asset('js/vehiculos.js') }}"></script>
@endpush
