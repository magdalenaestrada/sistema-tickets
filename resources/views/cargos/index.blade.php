@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de cargos</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevaCargo">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Cargo
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaCargos" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Rol</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('cargos.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/cargos.js') }}"></script>
@endpush
