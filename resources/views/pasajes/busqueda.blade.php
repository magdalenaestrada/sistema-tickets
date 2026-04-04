@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Búsqueda de Pasajes</h5>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('pasajes.listar') }}" class="row g-3 mb-4" id="formFiltros">
                <div class="col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="text" name="documento" id="filtroDNI" class="form-control" placeholder="Buscar DNI"
                        value="{{ request('documento') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="filtroFecha" class="form-control"
                        value="{{ request('fecha') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Origen</label>
                    <select name="origen_id" id="filtroOrigen">
                        <option value="">Todos</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}" {{ request('origen_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Destino</label>
                    <select name="destino_id" id="filtroDestino">
                        <option value="">Todos</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}" {{ request('destino_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="V" {{ request('estado') == 'V' ? 'selected' : '' }}>Vendido</option>
                        <option value="F" {{ request('estado') == 'F' ? 'selected' : '' }}>Abordó</option>
                        <option value="X" {{ request('estado') == 'X' ? 'selected' : '' }}>No abordó / Cancelado
                        </option>
                    </select>
                </div>
                <button type="button" class="btn btn-secondary" id="btnLimpiar">
                    Limpiar
                </button>
            </form>

            <div id="contenedorResultados">
                @include('pasajes.partials.tabla', ['pasajes' => $pasajes])
            </div>
        </div>
    @endsection

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <script src="{{ asset('js/pasajes_busqueda.js') }}"></script>
    @endpush
