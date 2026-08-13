@extends('layouts.app')

@section('content')
    <div class="row g-3">
        {{-- Columna Izquierda: Tabla y Gestión Principal --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h5 class="fw-bold mb-0 text-dark">Gestión de salidas</h5>
                        <button id="btnEliminarSeleccionados"
                            class="btn btn-danger btn-sm px-3 d-flex align-items-center gap-1">
                            <i class="link-icon" data-lucide="trash-2"></i>
                            Eliminar seleccionados
                        </button>
                    </div>
                    <p class="text-muted fs-7 mb-0">Controla el avance del bus por cada sucursal y sus ventas.</p>
                </div>

                <div class="card-body">
                    <div
                        class="d-flex flex-wrap align-items-center justify-content-between bg-light p-2.5 rounded-3 mb-3 border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-7 fw-semibold text-muted me-1">Estados:</span>
                            <span
                                class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">PROGRAMADA</span>
                            <span
                                class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-8">EN
                                RUTA</span>
                            <span
                                class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-8">FINALIZADA</span>
                            <span
                                class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-8">CANCELADA</span>
                        </div>
                        <small class="text-muted fs-8 d-none d-md-inline">Al iniciar el viaje, se bloquean nuevas
                            ventas</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 text-muted">
                                    <i data-lucide="filter" style="width: 15px;"></i>
                                </span>
                                <select id="filtroEstado" class="form-select bg-light border-0 fs-7">
                                    <option value="">Todos los estados</option>
                                    <option value="programado">Programado</option>
                                    <option value="en_ruta">En ruta</option>
                                    <option value="finalizado">Finalizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 text-muted">
                                    <i data-lucide="map-pin" style="width: 15px;"></i>
                                </span>
                                <select id="filtroRuta" class="form-select bg-light border-0 fs-7">
                                    <option value="">Todas las rutas</option>
                                    @foreach ($rutas as $ruta)
                                        <option value="{{ $ruta->id }}">{{ $ruta->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tablaSalidas" class="table table-hover align-middle w-100 text-nowrap">
                            <thead class="bg-light text-muted fw-semibold">
                                <tr>
                                    <th width="30"><input type="checkbox" id="chk-todos" class="form-check-input"></th>
                                    <th>ID</th>
                                    <th>RUTA</th>
                                    <th>FECHA</th>
                                    <th>SALIDA PROGRAMADA</th>
                                    <th>LLEGADA PROGRAMADA</th>
                                    <th>ESTADO</th>
                                    <th class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>


            </div>
        </div>

        {{-- Columna Derecha: Panel Lateral (Detalles en Vivo) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 id="tituloPanelSalida" class="fw-bold mb-0">Detalle de salida</h6>
                    <span id="badgeEnVivo"
                        class="badge bg-success-subtle text-success fw-bold d-flex align-items-center gap-1">
                        <span class="spinner-grow spinner-grow-sm" role="status"></span> EN VIVO
                    </span>
                </div>

                <div class="card-body p-3" id="panelSalidaContenido">
                    {{-- Carga inicial vacía --}}
                    <p class="text-muted text-center py-4">Selecciona una salida para ver la información en tiempo real.</p>
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
        window.IS_ADMIN = {{ auth()->user()->hasRole('Administrador') ? 'true' : 'false' }};

        window.SUCURSALES = @json(\App\Models\Sucursal::select('id', 'nombre_comercial')->get());
        window.USER_SUCURSAL = @json(auth()->user()->sucursal ? auth()->user()->sucursal->only('id', 'nombre_comercial') : null);
    </script>
    <script src="{{ asset('js/salidas.js') }}"></script>
@endpush
