@extends('layouts.app')
<style>
    input[type="file"]::file-selector-button {
        background-color: #4a4a4a;
        /* morado */
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
    }
</style>
@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-medium">DATOS DE LA EMPRESA</h5>
        </div>
        <div class="card-body">
            <form id="formEmpresa" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="empresa_id" name="empresa_id" value="{{ $empresa->id ?? '' }}">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">RUC / Documento <span style="color: red">*</span></label>
                        <div class="input-group">
                            <input type="text" name="documento" id="documento" class="form-control" required
                                maxlength="11" inputmode="numeric" pattern="[0-9]{11}"
                                value="{{ $empresa->documento ?? '' }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11)">
                            <button class="btn btn-outline-primary" type="button" id="btnBuscarRuc">
                                <i class="link-icon" data-lucide="search"></i> </button>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Razón Social <span style="color: red">*</span></label>
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
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        Guardar
                    </button>

                    <button type="button" class="btn btn-warning d-none" id="btnEditar">
                        Editar
                    </button>

                    <button type="button" class="btn btn-secondary d-none" id="btnCancelar">
                        Cancelar
                    </button>
                </div>
            </form>

        </div>
    </div>

    @if (isset($empresa))
        <br>
        <div class="card py-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">Sucursales de {{ $empresa->razon_social }}</h5>
                <button class="btn btn-primary" id="btnNuevaSucursal">
                    <i class="fa fa-plus"></i> Nueva Sucursal
                </button>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <select id="filtro_departamento_id" class="form-select">
                            <option value="">Filtrar por departamento</option>
                            @foreach ($departamentos as $departamento)
                                <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <select id="filtro_provincia_id" class="form-select">
                            <option value="">Filtrar por provincia</option>
                            @foreach ($provincias as $provincia)
                                <option value="{{ $provincia->id }}">{{ $provincia->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <select id="filtro_distrito_id" class="form-select">
                            <option value="">Filtrar por distrito</option>
                            @foreach ($distritos as $distrito)
                                <option value="{{ $distrito->id }}">{{ $distrito->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="text" id="nombre_sucursal" name="nombre_sucursal" class="form-control"
                            placeholder="Filtrar por sucursal">
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button class="btn btn-secondary" id="btnLimpiarFiltros">
                            <i class="fa fa-filter"></i> Limpiar
                        </button>
                    </div>
                </div>
                <table id="tablaSucursales" class="table table-hover w-100 border rounded">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Distrito</th>
                            <th>Nombre Sucursal</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        @include('empresas.modals.create_sucursal')
    @endif
@endsection

@push('scripts')
    @if (isset($empresa))
        <script>
            const EMPRESA_ID = {{ $empresa->id }};
        </script>
        <script src="{{ asset('js/sucursales.js') }}"></script>
    @endif

    <script src="{{ asset('js/empresas.js') }}"></script>
@endpush
