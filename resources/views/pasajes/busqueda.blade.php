@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <input type="text" id="filtroDNI" class="form-control" placeholder="Buscar documento">
                </div>

                <div class="col-md-2">
                    <input type="date" id="filtroFecha" class="form-control">
                </div>

                <div class="col-md-2">
                    <select id="filtroOrigen" class="form-select">
                        <option value="">Buscar origen</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filtroDestino" class="form-select">
                        <option value="">Buscar destino</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-header d-flex justify-content-end">
                    <button class="btn btn-success" id="btnNueva">
                        Exportar Excel
                    </button>
                </div>

            </div>
            @include('pasajes.partials.tabla')
            @include('pasajes.modals.show')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pasajes_busqueda.js') }}"></script>
@endpush
