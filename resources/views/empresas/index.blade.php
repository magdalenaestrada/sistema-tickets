@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Datos de la Empresa</h5>
        </div>
        <div class="card-body">
            <form id="formEmpresa" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="empresa_id" name="empresa_id" value="{{ $empresa->id ?? '' }}">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">RUC / Documento</label>
                        <div class="input-group">
                            <input type="text" name="documento" id="documento" class="form-control"
                                value="{{ $empresa->documento ?? '' }}" required>
                            <button class="btn btn-outline-primary" type="button" id="btnBuscarRuc">
                                <i class="link-icon" data-lucide="search"></i> </button>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Razón Social</label>
                        <input type="text" class="form-control" name="razon_social" id="razon_social"
                            value="{{ $empresa->razon_social ?? '' }}" required>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Nombre Comercial</label>
                        <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial"
                            value="{{ $empresa->nombre_comercial ?? '' }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion"
                            value="{{ $empresa->direccion ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">

                        @if (isset($empresa) && $empresa->logo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" style="max-height:80px">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Usuario Facturación</label>
                        <input type="text" class="form-control" id="usuario_facturacion" name="usuario_facturacion"
                            value="{{ $empresa->usuario_facturacion ?? '' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contraseña Facturación</label>
                        <input type="password" class="form-control" id="contrasena_facturacion"
                            name="contrasena_facturacion" value="{{ $empresa->contrasena_facturacion ?? '' }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        Guardar
                    </button>

                    <button type="button" class="btn btn-warning d-none" id="btnEditar">
                        Editar
                    </button>
                </div>
            </form>

        </div>
    </div>

    @if (isset($empresa))
        <br>
        <div class="card py-2">
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

        @include('empresas.modals.create_sucursal')
        @push('scripts')
            <script>
                const EMPRESA_ID = {{ $empresa->id }};
            </script>
            <script src="{{ asset('js/sucursales.js') }}"></script>
        @endpush
    @endif
@endsection
@push('scripts')
    <script src="{{ asset('js/empresas.js') }}"></script>
@endpush
