@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Asignar Encomiendas</h5>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-2">
                    <input type="text" id="filtroDNI" class="form-control" placeholder="DNI del emisor">
                </div>

                <div class="col-md-2">
                    <select id="filtroOrigen" class="form-select">
                        <option value="">Filtrar por origen</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="filtroDestino" class="form-select">
                        <option value="">Filtrar por destino</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <select id="asignacion_id" class="form-select" required>
                        <option value="">Seleccione una salida</option>
                        @foreach ($asignaciones as $a)
                            <option value="{{ $a->id }}">{{ $a->horario->tipo_vehiculo->descripcion }} |
                                {{ $a->horario->fecha_formateada }} |
                                {{ $a->horario->hora_formateada }} | {{ $a->horario->punto_origen->nombre_comercial }} ->
                                {{ $a->horario->punto_destino->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-success w-100" id="btnAsignar">
                        Asignar
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <table class="table table-bordered table-hover w-100" id="tablaEncomiendas">
                <thead>
                    <tr>
                        <th width="50px">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>ID</th>
                        <th>DNI Emisor</th>
                        <th>Emisor</th>
                        <th>Receptor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mt-3">
                <span class="badge bg-info" id="contadorSeleccionados">0 seleccionadas</span>
            </div>
        </div>
    </div>
    @include('encomiendas.modals.ver')
@endsection

@push('scripts')
    <script>
        const csrf_token = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/encomiendas.js') }}"></script>
@endpush
