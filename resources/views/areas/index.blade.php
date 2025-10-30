@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de areas</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevaArea">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir Area
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaAreas" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('areas.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/areas.js') }}"></script>
@endpush
