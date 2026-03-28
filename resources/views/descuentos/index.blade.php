@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de Cupones</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevoDescuento"> <i class="link-icon" data-lucide="plus"></i>
                    Añadir Cupón
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2 mb-3 mt-3">
                    <input type="text" id="filtroCodigo" class="form-control" placeholder="Buscar código">
                </div>
                <div class="col-md-3 mb-3 mt-3">
                    <select id="filtroTipoCupon">
                        <option value="">Buscar por tipo de cupón</option>
                        @foreach ($tipo_cupones as $tipo)
                            <option value="{{ $tipo->descripcion }}">{{ $tipo->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 mt-3">
                    <select id="filtroPersona">
                        <option value="">Buscar por persona</option>
                        @foreach ($empleados as $empleado)
                            <option value="{{ $empleado->persona->nombre_completo }}">
                                {{ $empleado->persona->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 mt-3">
                    <select id="filtroCargo">
                        <option value="">Buscar por cargo</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->id }}">{{ $cargo->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 mb-3 mt-3">
                    <button class="btn btn-primary" id="btnLimpiarFiltros">Limpiar</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablaDescuentos" class="table table-hover align-middle w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Tipo cupón</th>
                            <th>Código</th>
                            <th>Cantidad Usos</th>
                            <th>Fecha Máxima</th>
                            <th>Descuento</th>
                            <th>Estado</th>
                            <th class="text-center" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        @include('descuentos.modals.create')
    @endsection

    @push('scripts')
        <script src="{{ asset('js/descuentos.js') }}"></script>
    @endpush
