@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Tipos de Encomienda</h5>

            <button class="btn btn-primary btn-sm" id="btnNuevo">
                Nuevo Tipo
            </button>
        </div>

        <div class="card-body">
            <table class="table table-bordered" id="tablaTipos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Precio Base</th>
                        <th>Peso Límite</th>
                        <th>Costo Kg Extra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @include('tipo-encomienda.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/tipo_encomienda.js') }}"></script>
@endpush
