@extends('layouts.app')

@section('content')
    <div class="container">

        {{-- Tarjetas de tipo de reporte --}}
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card report-card" data-reporte="ventas">
                    <div class="card-body text-center">
                        <h6>Ventas</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card report-card" data-reporte="pasajeros">
                    <div class="card-body text-center">
                        <h6>Pasajeros</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card report-card" data-reporte="cupones">
                    <div class="card-body text-center">
                        <h6>Uso de Cupones</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card report-card" data-reporte="encomiendas">
                    <div class="card-body text-center">
                        <h6>Encomiendas</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card report-card" data-reporte="viajes">
                    <div class="card-body text-center">
                        <h6>Viajes</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card report-card" data-reporte="vehiculos">
                    <div class="card-body text-center">
                        <h6>Vehiculos</h6>
                    </div>
                </div>
            </div>
        </div>

        <div id="filtros-container" class="mb-3" style="display:none;">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <!-- Filtros comunes -->
                        <div class="col-md-3">
                            <label>Fecha inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Fecha fin</label>
                            <input type="date" id="fecha_fin" class="form-control">
                        </div>

                        <!-- Filtros ventas -->
                        <div class="col-md-2 filter-ventas" style="display:none;">
                            <label>Tipo Documento</label>
                            <select id="tipo_documento" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($tipos_documento as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 filter-ventas" style="display:none;">
                            <label>Sucursal</label>
                            <select id="sucursal" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 filter-ventas" style="display:none;">
                            <label>Estado</label>
                            <select id="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="E">Emitidos</option>
                                <option value="A">Anulados</option>
                            </select>
                        </div>
                        <div class="col-md-5 filter-ventas" style="display:none;">
                            <label>Cliente</label>
                            <input type="text" id="cliente" class="form-control" placeholder="Nombre cliente">
                        </div>
                        <div class="col-md-4 filter-ventas" style="display:none;">
                            <label>Vendedor</label>
                            <input type="text" id="vendedor" class="form-control" placeholder="Usuario vendedor">
                        </div>

                        <!-- Filtros viajes -->
                        <div class="col-md-3 filter-pasajes" style="display:none;">
                            <label>Tipo Vehículo</label>
                            <select id="tipo_vehiculo" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($tipos_vehiculo as $vehiculo)
                                    <option value="{{ $vehiculo->id }}">{{ $vehiculo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 filter-pasajes" style="display:none;">
                            <label>Tipo Viaje</label>
                            <select id="tipo_viaje" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($tipos_viaje as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 filter-pasajes" style="display:none;">
                            <label>Punto Origen</label>
                            <select id="punto_origen" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($puntos as $punto)
                                    <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 filter-pasajes" style="display:none;">
                            <label>Punto Destino</label>
                            <select id="punto_destino" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($puntos as $punto)
                                    <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 filter-pasajes" style="display:none;">
                            <label>Costo Pasaje</label>
                            <input type="number" step="0.01" id="costo_pasaje" class="form-control">
                        </div>
                        <div class="col-md-2 filter-pasajes" style="display:none;">
                            <label>Hora Embarque</label>
                            <input type="time" id="hora_embarque" class="form-control">
                        </div>
                        <div class="col-md-2 filter-pasajes" style="display:none;">
                            <label>Fecha Salida</label>
                            <input type="date" id="fecha_salida_pasaje" class="form-control">
                        </div>
                        <div class="col-md-6 filter-pasajes" style="display:none;">
                            <label>Días de la semana</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <div><input type="checkbox" id="lunes" value="1"> Lunes</div>
                                <div><input type="checkbox" id="martes" value="1"> Martes</div>
                                <div><input type="checkbox" id="miercoles" value="1"> Miércoles</div>
                                <div><input type="checkbox" id="jueves" value="1"> Jueves</div>
                                <div><input type="checkbox" id="viernes" value="1"> Viernes</div>
                                <div><input type="checkbox" id="sabado" value="1"> Sábado</div>
                                <div><input type="checkbox" id="domingo" value="1"> Domingo</div>
                            </div>
                        </div>

                        <!-- Botones export -->
                        <div class="col-12 mt-2 d-flex gap-2">
                            <button id="btnExcel" class="btn btn-success btn-sm">Exportar Excel</button>
                            <button id="btnPDF" class="btn btn-danger btn-sm">Exportar PDF</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="card mt-3">
            <div class="card-body">
                <table id="tablaReportes" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo Documento</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Sucursal</th>
                            <th>Estado</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

<style>
    .report-card {
        cursor: pointer;
        transition: all 0.2s;
        background-color: #fff;
    }

    .report-card:hover {
        background-color: #d0ebff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .report-card.selected {
        border: 2px solid #0d6efd;
        background-color: #c9e6fc;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    #tablaReportes {
        background-color: #e7f5ff;
    }

    #tablaReportes thead,
    #tablaReportes thead th {
        background-color: #cfe2ff !important;
        color: #000;
        text-align: center;
        vertical-align: middle;
    }

    #tablaReportes tbody tr:hover {
        background-color: #d0ebff !important;
    }

    #tablaReportes tbody td {
        text-align: center;
        vertical-align: middle;
    }
</style>
@push('scripts')
    <script src="{{ asset('js/reportes.js') }}"></script>
@endpush
