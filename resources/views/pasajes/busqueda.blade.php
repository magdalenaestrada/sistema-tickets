@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <button class="btn btn-primary" id="btnNueva">Exportar excel</button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label>DNI Emisor</label>
                    <input type="text" id="filtroDNI" class="form-control" placeholder="DNI del emisor">
                </div>

                <div class="col-md-2">
                    <label>Origen</label>
                    <select id="filtroOrigen" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Destino</label>
                    <select id="filtroDestino" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label>Salidas</label>
                    <select id="asignacion_id" class="form-select" required>
                        <option value="">-- Seleccione --</option>
                        @foreach ($asignaciones as $a)
                            <option value="{{ $a->id }}">{{ $a->horario->tipo_vehiculo->descripcion }} |
                                {{ $a->horario->fecha_formateada }} |
                                {{ $a->horario->hora_formateada }} | {{ $a->horario->punto_origen->nombre_comercial }} ->
                                {{ $a->horario->punto_destino->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <table class="table table-bordered table-hover w-100" id="tablaEncomiendas">
                <thead>
                    <tr>
                        <th width="50px">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @include('pasajes.partials.tabla')
@endsection

@push('scripts')
    <script>
        const csrf_token = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/pasajes.js') }}"></script>
@endpush
