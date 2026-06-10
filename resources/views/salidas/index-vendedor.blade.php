@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestión de salidas</h5>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todas</option>
                                <option value="programado">Programadas</option>
                                <option value="en_ruta">En ruta</option>
                                <option value="finalizado">Finalizadas</option>
                                <option value="cancelado">Canceladas</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input type="date" id="filtroFecha" class="form-control">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tablaSalidas" class="table table-hover align-middle w-100">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Ruta</th>
                                    <th>Fecha</th>
                                    <th>Salida</th>
                                    <th>Llegada</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 id="tituloPanelSalida">Detalle</h5>
                </div>
                <div class="card-body" id="panelSalidaContenido">
                    <p class="text-muted">Selecciona una salida</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.VEHICULOS = @json($vehiculos);
        window.CONDUCTORES = @json($conductores);
        window.HORARIOS_SALIDA = @json($horariosSalida);
    </script>

    <script src="{{ asset('js/salidas.js') }}"></script>
@endpush
