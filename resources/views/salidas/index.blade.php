@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestión de salidas</h5>

                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="modoGenerarSalidas()">
                            <i class="link-icon" data-lucide="calendar-plus"></i>
                            Programar varias salidas
                        </button>
                        <button class="btn btn-primary" onclick="modoCrearSalida()">
                            <i class="link-icon" data-lucide="plus"></i>
                            Crear salida única
                        </button>
                        <button id="btnEliminarSeleccionados" class="btn btn-danger"> <i class="link-icon"
                                data-lucide="trash-2"></i>
                            Eliminar seleccionados
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">


                        <div class="col-md-4">
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="programado">Programado</option>
                                <option value="en_ruta">En ruta</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="cancelado">Cancelado</option>
                                <option value="reprogramado">Reprogramado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <select id="filtroRuta">
                                <option value="">Todas las rutas</option>
                                @foreach ($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">
                                        {{ $ruta->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tablaSalidas" class="table table-hover align-middle w-100">
                            <thead class="table-primary">
                                <tr>
                                    <th><input type="checkbox" id="chk-todos"></th>
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
        window.RUTAS_SALIDA = @json($rutas);
        window.TIPOS_VEHICULO = @json($tiposVehiculo);
    </script>

    <script src="{{ asset('js/salidas.js') }}"></script>
@endpush
