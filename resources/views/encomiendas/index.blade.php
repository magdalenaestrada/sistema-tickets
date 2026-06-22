@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Encomiendas sin asignar</h5>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Documento</label>
                    <input type="text" id="filtroDocumento" class="form-control" placeholder="Emisor o receptor">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" id="filtroFecha" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Origen</label>
                    <select id="filtroOrigen">
                        <option value="">Todos</option>
                        @foreach ($pueblitos as $pueblito)
                            <option value="{{ $pueblito->id }}">{{ $pueblito->descripcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Destino</label>
                    <select id="filtroDestino">
                        <option value="">Todos</option>
                        @foreach ($pueblitos as $pueblito)
                            <option value="{{ $pueblito->id }}">{{ $pueblito->descripcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Salida a asignar</label>
                    <select id="salidaAsignar">
                        <option value="">Seleccione salida</option>

                        @foreach ($salidas as $salida)
                            <option value="{{ $salida->id }}">
                                {{ $salida->fecha_salida?->format('d/m/Y') }}
                                | {{ $salida->horario?->hora_formateada }}
                                | {{ $salida->horario?->ruta?->nombre ?? 'Ruta' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex justify-content-center align-items-center">
                    <button type="button" class="btn btn-light" id="btnLimpiar">
                        Limpiar
                    </button>
                </div>
                <div class="col-12 d-flex justify-content-between">

                    <button type="button" class="btn btn-primary" id="btnAsignarSeleccionadas">
                        Asignar seleccionadas
                    </button>
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
                            <th>Fecha</th>
                            <th>Emisor</th>
                            <th>DNI Emisor</th>
                            <th>Receptor</th>
                            <th>DNI Receptor</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>
                </table>
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
