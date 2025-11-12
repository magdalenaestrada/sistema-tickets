@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h4>Gestión de Encomiendas</h4>
            <button class="btn btn-primary" id="btnNueva">Nueva Encomienda</button>
        </div>

        <table class="table table-bordered table-striped" id="tablaEncomiendas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Emisor</th>
                    <th>Receptor</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>



    @include('encomiendas.modals.form')
    @include('encomiendas.modals.ver')
@endsection

@push('scripts')
    <script src="{{ asset('js/encomiendas.js') }}"></script>
@endpush
