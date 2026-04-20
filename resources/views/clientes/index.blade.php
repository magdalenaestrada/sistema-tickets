@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Clientes</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevoCliente">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Cliente
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="filtroTipoDocumento" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="1">DNI</option>
                        <option value="2">RUC</option>
                        <option value="3">PASAPORTE</option>
                        <option value="4">CARNET DE EXTRANJERÍA</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="text" id="filtroDocumento" class="form-control"
                        placeholder="Buscar número de documento">
                </div>

                <div class="col-md-4">
                    <input type="text" id="filtroNombres" class="form-control"
                        placeholder="Buscar razón social / nombres">
                </div>
            </div>

            <table id="tablaClientes" class="table table-hover w-100">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>DOCUMENTO</th>
                        <th>RAZÓN SOCIAL</th>
                        <th>TELÉFONO</th>
                        <th>CELULAR</th>
                        <th>CORREO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    @include('clientes.modals.create')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/clientes.js') }}"></script>
@endpush
