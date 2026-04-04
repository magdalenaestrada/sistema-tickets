@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Encomiendas asignadas</h5>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" id="btnLimpiar">
                    Limpiar filtros
                </button>

                <button type="button" class="btn btn-primary" id="btnConfirmarLlegada">
                    Confirmar llegada
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <input type="text" id="filtroDocumento" class="form-control" placeholder="Buscar por documento">
                </div>

                <div class="col-md-3">
                    <select id="filtroOrigen">
                        <option value="">Todos los orígenes</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="filtroDestino">
                        <option value="">Todos los destinos</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="filtroSalida">
                        <option value="">Todas las salidas</option>
                        @foreach ($asignaciones as $asignacion)
                            <option value="{{ $asignacion->id }}">
                                {{ optional($asignacion->horario)->hora_salida ?? 'Sin horario' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover w-100" id="tablaEncomiendas">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Receptor</th>
                            <th>DNI Receptor</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="mt-3">
                <span class="badge bg-info" id="contadorSeleccionados">0 seleccionadas</span>
            </div>
        </div>
    </div>

    @include('encomiendas.modals.ver')
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/encomiendas_asignadas.js') }}"></script>
@endpush
