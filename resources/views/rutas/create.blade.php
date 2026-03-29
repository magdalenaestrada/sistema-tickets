@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Crear Ruta</h5>
            <a href="{{ route('rutas.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left"></i>
                Volver
            </a>
        </div>
        <div class="card-body">
            <form id="formRuta">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nombre de la ruta</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Ica - Lima"
                        required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Puntos de la ruta</label>
                    <div id="contenedorPuntos"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="agregarPunto()">
                        <i data-lucide="plus"></i> Agregar punto
                    </button>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save"></i>
                        Guardar Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/crear_rutas.js') }}"></script>
@endpush
