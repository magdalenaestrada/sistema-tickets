@extends('layouts.app')
<style>
    .ts-control {
        border-radius: 0.375rem;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
        padding: 6px;
        min-height: 38px;
    }

    .ts-control .item {
        background-color: var(--bs-primary);
        color: var(--bs-white);
        border-radius: 20px;
        padding: 2px 10px;
        margin: 2px;
        font-size: 13px;
    }

    .ts-control .remove {
        margin-left: 6px;
        color: var(--bs-white);
    }

    .ts-dropdown {
        border-radius: 0.375rem;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    .ts-dropdown .option {
        background: var(--bs-body-bg);
    }

    .ts-dropdown .option.active {
        background-color: var(--bs-primary);
        color: var(--bs-white);
    }
</style>

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Cupones</h5>
            <div class="d-flex gap-2"> <button class="btn btn-primary" id="btnNuevoDescuento"> <i class="link-icon"
                        data-lucide="plus"></i>
                    Añadir Cupón </button> </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" id="filtroTipoCupon" class="form-control" placeholder="Buscar por tipo">
                </div>
                <div class="col-md-3">
                    <input type="text" id="filtroCodigo" class="form-control" placeholder="Buscar código">
                </div>
                <div class="col-md-4">
                    <input type="text" id="filtroPersona" class="form-control" placeholder="Buscar nombres">
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablaDescuentos" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo cupón</th>
                            <th>Código</th>
                            <th>Persona</th>
                            <th>Cantidad Usos</th>
                            <th>Fecha Máxima</th>
                            <th>Monto Descuento</th>
                            <th>Porcentaje</th>
                            <th>Activo</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('descuentos.modals.create')
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/descuentos.js') }}"></script>
@endpush
