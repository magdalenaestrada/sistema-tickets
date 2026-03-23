@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Encomiendas</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 g-2">
                <div class="col-md-6">
                    <input type="text" id="filtroReceptor" class="form-control"
                        placeholder="Buscar por documento">
                </div>
            </div>
            <table class="table table-bordered table-hover w-100" id="tablaEncomiendas">
                <thead>
                    <tr>

                        <th>ID</th>
                        <th>DNI Receptor</th>
                        <th>Receptor</th>
                        <th>Emisor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mt-3">
                <span class="badge bg-info" id="contadorSeleccionados">0 seleccionadas</span>
            </div>
        </div>
    </div>
    @include('encomiendas.modals.ver')
@endsection

@push('scripts')
    <script>
        const csrf_token = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('js/encomiendas_estado.js') }}"></script>
@endpush
