@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestión de empresas</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" id="btnNuevaEmpresa">
                <i class="fa-solid fa-plus"></i> Añadir Empresa
            </button>
        </div>
    </div>

    <div class="card-body">
        <table id="tablaEmpresas" class="table table-striped w-100"></table>
    </div>
</div>

@include('empresas.modals.create')
@endsection

@push('scripts')
<script src="{{ asset('js/empresas.js') }}"></script>
@endpush
