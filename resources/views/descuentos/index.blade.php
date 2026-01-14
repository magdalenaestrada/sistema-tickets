@extends('layouts.app')

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
    <script src="{{ asset('js/descuentos.js') }}"></script>
@endpush
