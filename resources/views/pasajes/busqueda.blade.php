@extends('layouts.app')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <!-- Cabecera de la tarjeta con estilo limpio -->
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-1">Búsqueda de Pasajes</h4>
            <p class="text-muted small mb-0">Administra y supervisa el estado de los pasajes y reservas.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Filtros con inputs limpios y sutiles -->
        <form method="GET" action="{{ route('pasajes.listar') }}" class="row g-3 align-items-end mb-4" id="formFiltros">
            <div class="col-md-2">
                <label class="form-label fw-semibold text-secondary small">DNI</label>
                <input type="text" name="documento" id="filtroDNI" class="form-control bg-light border-0 py-2" placeholder="Buscar DNI" value="{{ request('documento') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold text-secondary small">Fecha</label>
                <input type="date" name="fecha" id="filtroFecha" class="form-control bg-light border-0 py-2" value="{{ request('fecha') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-secondary small">Origen</label>
                <select name="origen_id" id="filtroOrigen" class="form-select bg-light border-0 py-2">
                    <option value="">Todos</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('origen_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre_comercial }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-secondary small">Destino</label>
                <select name="destino_id" id="filtroDestino" class="form-select bg-light border-0 py-2">
                    <option value="">Todos</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('destino_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre_comercial }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold text-secondary small">Estado</label>
                <select name="estado" id="filtroEstado" class="form-select bg-light border-0 py-2">
                    <option value="">Todos</option>
                    <option value="V" {{ request('estado') == 'V' ? 'selected' : '' }}>Vendido</option>
                    <option value="R" {{ request('estado') == 'R' ? 'selected' : '' }}>Reservado</option>
                    <option value="X" {{ request('estado') == 'X' ? 'selected' : '' }}>Anulado</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-light text-secondary border-0 px-3 py-2 fw-medium" id="btnLimpiar">
                    <i data-lucide="rotate-ccw" class="align-text-bottom me-1" style="width: 16px; height: 16px;"></i> Limpiar filtros
                </button>
            </div>
        </form>

        <!-- Contenedor de la tabla -->
        <div id="contenedorResultados">
            @include('pasajes.partials.tabla', ['pasajes' => $pasajes])
        </div>
    </div>
</div>

@include('pasajes.modals.sobrequipaje')
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/pasajes_busqueda.js') }}"></script>
    <script>
        window.VENTA_CONFIG = {
            esAdmin: @json($esAdmin),
            cajaAbierta: @json($cajaAbierta),
            rutaCaja: @json($ruta),
        };
    </script>
@endpush