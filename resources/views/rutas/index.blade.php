@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>Lista de rutas</h5>

                    <button class="btn btn-primary" onclick="modoCrear()">
                        <i data-lucide="plus"></i> Nueva ruta
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaRutas" class="table table-hover w-100">
                            <thead class="table-primary">
                                <tr>
                                    <td>ID</td>
                                    <td>NOMBRE</td>
                                    <td>PUNTOS</td>
                                    <td>ESTADO</td>
                                    <td>ACCIONES</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 id="tituloPanel">Detalle</h5>
                </div>

                <div class="card-body" id="panelContenido">
                    <p class="text-muted">Selecciona una ruta</p>
                </div>

                <div id="contenedorTramos"></div>

            </div>
        </div>

    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="{{ asset('js/rutas.js') }}"></script>
@endpush
