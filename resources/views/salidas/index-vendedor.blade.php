@extends('layouts.app')

@section('content')
    <style>
        .salidas-page {
            --salida-primary: #2563eb;
            --salida-border: #e7eaf0;
            --salida-muted: #64748b;
            --salida-bg: #f8fafc;
        }

        .salidas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .salidas-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .salidas-title-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eff6ff;
            color: var(--salida-primary);
        }

        .salidas-title-icon svg {
            width: 23px;
            height: 23px;
        }

        .salidas-title h4 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .salidas-title p {
            margin: 2px 0 0;
            font-size: 12px;
            color: var(--salida-muted);
        }

        /* ESTADOS */

        .salidas-estados {
            display: flex;
            gap: 6px;
            border-bottom: 1px solid var(--salida-border);
            overflow-x: auto;
            margin-bottom: 16px;
        }

        .salida-estado-tab {
            border: 0;
            background: transparent;
            padding: 11px 13px;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            position: relative;
        }

        .salida-estado-tab svg {
            width: 16px;
            height: 16px;
        }

        .salida-estado-tab .contador {
            font-size: 11px;
            font-weight: 700;
        }

        .salida-estado-tab.active {
            color: var(--salida-primary);
        }

        .salida-estado-tab.active::after {
            content: "";
            position: absolute;
            height: 2px;
            left: 8px;
            right: 8px;
            bottom: -1px;
            background: var(--salida-primary);
            border-radius: 4px;
        }

        /* FILTROS */

        .salidas-filtros {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .salidas-filtros .form-select,
        .salidas-filtros .form-control {
            font-size: 12px;
            min-height: 37px;
            border-color: var(--salida-border);
        }

        /* TABLA */

        #tablaSalidas thead th {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: .03em;
            padding: 12px 14px;
            border-bottom: 1px solid var(--salida-border);
        }

        #tablaSalidas tbody td {
            padding: 14px;
            font-size: 12px;
            border-color: #f0f2f5;
            vertical-align: middle;
        }

        #tablaSalidas tbody tr {
            cursor: pointer;
        }

        #tablaSalidas tbody tr:hover {
            background: #f8fbff;
        }

        #tablaSalidas tbody tr.salida-seleccionada {
            background: #eff6ff !important;
        }

        .hora-salida {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .ruta-principal {
            font-weight: 600;
            color: #1e293b;
        }

        .ruta-secundaria {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
        }

        /* BADGES */

        .estado-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .estado-programado {
            background: #eff6ff;
            color: #2563eb;
        }

        .estado-en_ruta {
            background: #ecfdf3;
            color: #16a34a;
        }

        .estado-finalizado {
            background: #f1f5f9;
            color: #475569;
        }

        .estado-cancelado {
            background: #fff1f2;
            color: #e11d48;
        }

        /* PANEL DERECHO */

        .detalle-salida-card {
            position: sticky;
            top: 75px;
        }

        .detalle-placeholder {
            text-align: center;
            padding: 50px 20px;
            color: #94a3b8;
        }

        .detalle-placeholder svg {
            width: 30px;
            height: 30px;
            margin-bottom: 12px;
        }

        .detalle-ruta {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .detalle-meta {
            font-size: 11px;
            color: #64748b;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detalle-seccion {
            border-top: 1px solid var(--salida-border);
            margin-top: 17px;
            padding-top: 17px;
        }

        .detalle-seccion-title {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 13px;
        }

        /* RECORRIDO */

        .recorrido-item {
            display: grid;
            grid-template-columns: 20px 1fr auto;
            gap: 8px;
            position: relative;
            padding-bottom: 15px;
        }

        .recorrido-item:not(:last-child)::after {
            content: "";
            position: absolute;
            width: 1px;
            background: #e2e8f0;
            top: 17px;
            bottom: -2px;
            left: 7px;
        }

        .recorrido-dot {
            margin-top: 4px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: 1px solid #cbd5e1;
            background: white;
            z-index: 2;
        }

        .recorrido-item.actual .recorrido-dot {
            background: var(--salida-primary);
            border-color: var(--salida-primary);
        }

        .recorrido-nombre {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        .recorrido-sucursal {
            font-size: 10px;
            color: #94a3b8;
        }

        .recorrido-hora {
            font-size: 10px;
            color: #64748b;
        }
    </style>

    <div class="salidas-page">

        <div class="salidas-header">

            <div class="salidas-title">
                <div class="salidas-title-icon">
                    <i data-lucide="bus-front"></i>
                </div>

                <div>
                    <h4>Salidas</h4>
                    <p>Gestión de salidas programadas y manifiestos</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i data-lucide="plus" style="width:15px"></i>
                    Crear salida
                </button>

                <button class="btn btn-primary btn-sm">
                    <i data-lucide="calendar-plus" style="width:15px"></i>
                    Programar salidas
                </button>
            </div>

        </div>


        <div class="row g-3">

            <div class="col-xl-8">

                <div class="card border-0 shadow-sm rounded-3">

                    <div class="card-body p-0">

                        <div class="salidas-estados px-3 pt-2">

                            <button class="salida-estado-tab active" data-estado="">
                                <i data-lucide="list"></i>
                                Todas
                                <span class="contador" id="contadorTodas">0</span>
                            </button>

                            <button class="salida-estado-tab" data-estado="programado">
                                <i data-lucide="clock-3"></i>
                                Programadas
                                <span class="contador" id="contadorProgramadas">0</span>
                            </button>

                            <button class="salida-estado-tab" data-estado="en_ruta">
                                <i data-lucide="circle-play"></i>
                                En ruta
                                <span class="contador" id="contadorEnRuta">0</span>
                            </button>

                            <button class="salida-estado-tab" data-estado="finalizado">
                                <i data-lucide="circle-check"></i>
                                Finalizadas
                                <span class="contador" id="contadorFinalizadas">0</span>
                            </button>

                            <button class="salida-estado-tab" data-estado="cancelado">
                                <i data-lucide="circle-x"></i>
                                Canceladas
                                <span class="contador" id="contadorCanceladas">0</span>
                            </button>

                        </div>


                        <div class="px-3">

                            <div class="salidas-filtros">

                                <select id="filtroRuta" class="form-select" style="max-width:230px">

                                    <option value="">Todas las rutas</option>

                                    @foreach ($rutas as $ruta)
                                        <option value="{{ $ruta->id }}">
                                            {{ $ruta->nombre }}
                                        </option>
                                    @endforeach

                                </select>


                                <input type="date" id="filtroFecha" class="form-control" style="max-width:180px"
                                    value="{{ now('America/Lima')->format('Y-m-d') }}">

                            </div>

                        </div>

                        <div class="salidas-estados px-3 pt-2">

                            <button type="button" class="salida-estado-tab active" data-estado="">
                                <i data-lucide="list"></i>

                                Todas

                                <span class="contador">
                                    {{ $contadorSalidas['todas'] }}
                                </span>
                            </button>


                            <button type="button" class="salida-estado-tab" data-estado="programado">
                                <i data-lucide="clock-3"></i>

                                Programadas

                                <span class="contador">
                                    {{ $contadorSalidas['programadas'] }}
                                </span>
                            </button>


                            <button type="button" class="salida-estado-tab" data-estado="en_ruta">
                                <i data-lucide="circle-play"></i>

                                En ruta

                                <span class="contador">
                                    {{ $contadorSalidas['en_ruta'] }}
                                </span>
                            </button>


                            <button type="button" class="salida-estado-tab" data-estado="finalizado">
                                <i data-lucide="circle-check"></i>

                                Finalizadas

                                <span class="contador">
                                    {{ $contadorSalidas['finalizadas'] }}
                                </span>
                            </button>


                            <button type="button" class="salida-estado-tab" data-estado="cancelado">
                                <i data-lucide="circle-x"></i>

                                Canceladas

                                <span class="contador">
                                    {{ $contadorSalidas['canceladas'] }}
                                </span>
                            </button>

                        </div>

                        <div class="table-responsive">

                            <table id="tablaSalidas" class="table align-middle w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>HORA</th>
                                        <th>RUTA</th>
                                        <th>VEHÍCULO</th>
                                        <th>LLEGADA</th>
                                        <th>ESTADO</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-xl-4">

                <div class="card border-0 shadow-sm rounded-3 detalle-salida-card">

                    <div class="card-header bg-white py-3">
                        <h6 id="tituloPanelSalida" class="fw-bold mb-0">
                            Detalle de salida
                        </h6>
                    </div>

                    <div class="card-body p-3" id="panelSalidaContenido">

                        <div class="detalle-placeholder">

                            <i data-lucide="mouse-pointer-click"></i>

                            <div class="fw-semibold text-dark mb-1">
                                Selecciona una salida
                            </div>

                            <div style="font-size:11px">
                                Aquí podrás consultar el recorrido,
                                vehículo, conductor y manifiestos.
                            </div>

                        </div>

                    </div>

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
