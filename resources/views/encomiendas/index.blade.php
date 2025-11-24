@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Tipos de Encomienda</h5>
            <button class="btn btn-primary" id="btnNueva">Nueva Encomienda</button>
        </div>
        <div class="card-body">

            <table class="table table-bordered" id="tablaEncomiendas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Emisor</th>
                        <th>Receptor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @include('encomiendas.modals.ver')
@endsection

@push('scripts')
    <script src="{{ asset('js/encomiendas.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let ticketId = localStorage.getItem("ticket_encomienda_id");

            if (ticketId) {
                window.open(`/encomiendas/ticket/${ticketId}`, "_blank",
                    "width=420,height=650,noopener,noreferrer");

                localStorage.removeItem("ticket_encomienda_id");
            }

        });
    </script>
@endpush
