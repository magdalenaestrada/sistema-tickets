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
                    <label>Buscar por N. Documento</label>
                    <input type="text" id="filtroDocumento" class="form-control" placeholder="Ingresar N. Documento">
                </div>

                <div class="col-md-4">
                    <label>Buscar por nombres</label>
                    <input type="text" id="filtroNombres" class="form-control" placeholder="Ingresar nombres">
                </div>
            </div>

            <table id="tablaClientes" class="table table-striped w-100"></table>
        </div>
    </div>
    @include('clientes.modals.create')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="{{ asset('js/clientes.js') }}"></script>
@endpush
