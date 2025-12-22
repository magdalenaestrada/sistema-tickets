@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Empleados</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevoEmpleado">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Empleado
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3">
                    <input type="text" id="filtroDni" class="form-control" placeholder="Buscar DNI">
                </div>
                <div class="col-md-4">
                    <input type="text" id="filtroSucursal" class="form-control" placeholder="Buscar Sucursal">
                </div>
                <div class="col-md-4">
                    <input type="text" id="filtroCargo" class="form-control" placeholder="Buscar Cargo">
                </div>
            </div>

            <table id="tablaEmpleados" class="table table-striped w-100"></table>
        </div>
    </div>
    @include('empleados.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/empleados.js') }}"></script>
@endpush
