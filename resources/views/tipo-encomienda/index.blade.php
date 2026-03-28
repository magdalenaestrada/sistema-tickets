@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">

            <h5 class="mb-0">Tipos de Encomienda</h5>

            <button class="btn btn-primary btn-sm" id="btnNuevo">
                <i class="link-icon" data-lucide="plus"></i>
                Nuevo Tipo
            </button>
        </div>

        <div class="card-body">
            <table class="table table-hover" id="tablaTipos">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Precio Base (S/)</th>
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
