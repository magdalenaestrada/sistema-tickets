@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de permisos</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevaPermiso">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Permiso
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaPermisos" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('permisos.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/permisos.js') }}"></script>
@endpush
