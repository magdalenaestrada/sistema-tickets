@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Encomiendas sin asignar</h5>
        </div>

        <div class="card-body">

            <div class="row g-3 mb-3">

                <div class="col-md-6">
                    <div class="border rounded p-3 h-80">
                        <h6 class="text-uppercase fw-bold mb-3">Buscar encomienda</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Documento</label>
                                <input type="text" id="filtroDocumento" class="form-control"
                                    placeholder="Emisor o receptor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Origen</label>
                                <select id="filtroOrigen">
                                    <option value="">Todos</option>
                                    @foreach ($pueblitos as $pueblito)
                                        <option value="{{ $pueblito->id }}">{{ $pueblito->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destino</label>
                                <select id="filtroDestino">
                                    <option value="">Todos</option>
                                    @foreach ($pueblitos as $pueblito)
                                        <option value="{{ $pueblito->id }}">{{ $pueblito->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-xs btn-light" id="btnLimpiar">
                                    <i data-lucide="brush-cleaning"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 h-80">
                        <h6 class="text-uppercase fw-bold mb-3">Asignar encomienda</h6>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label">Fecha salida</label>
                                <input type="date" id="filtroFechaSalida" class="form-control">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Horario a asignar</label>
                                <select id="salidaAsignar">
                                    <option value="">Seleccione una fecha</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="border rounded p-3 h-80 d-flex flex-column" id="boxPuntosRuta" style="display:none;">
                        <h6 class="text-uppercase fw-bold mb-2">
                            Paradas </h6>
                        <div class="overflow-auto" style="max-height:100px;">
                            <ul class="mb-0 ps-3" id="listaPuntosRuta"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tablaEncomiendas">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>ID</th>
                            <th>Recorrido</th>
                            <th>Emisor</th>
                            <th>Receptor</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary btn-lg" id="btnAsignarSeleccionadas">
                    Asignar seleccionadas
                </button>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/encomiendas_no_asignadas.js') }}"></script>
@endpush
