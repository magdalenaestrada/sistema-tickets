@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de tipos de cupones</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" id="btnNuevoTipoCupon">
                    <i class="link-icon" data-lucide="plus"></i>
                    Añadir tipo de cupon
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaTipoCupones" class="table table-hover align-middle w-100">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('tipo_cupon.modals.create')
@endsection

@push('scripts')
    <script src="{{ asset('js/tipo_cupon.js') }}"></script>
@endpush
