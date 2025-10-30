@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sucursales de {{ $empresa->razon_social }}</h5>
        <button class="btn btn-primary" id="btnNuevaSucursal">
            <i class="fa fa-plus"></i> Nueva Sucursal
        </button>
    </div>

    <div class="card-body">
        <table id="tablaSucursales" class="table table-striped w-100"></table>
    </div>
</div>

@include('sucursales.modals.create')
@endsection

@push('scripts')
<script>
    const EMPRESA_ID = {{ $empresa->id }};
</script>
<script src="{{ asset('js/sucursales.js') }}"></script>
@endpush
