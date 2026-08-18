@extends('layouts.app')

@section('title', 'Dashboard Vendedor')

@section('content')
    <div class="container-fluid py-4 px-3 px-lg-4 min-vh-100">

        {{-- ===================================================== --}}
        {{-- ENCABEZADO Y FILTROS --}}
        {{-- ===================================================== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">

                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3">

                    <div>
                        @php
                            $horaActual = now('America/Lima')->hour;

                            $saludo = match (true) {
                                $horaActual >= 5 && $horaActual < 12 => 'BUENOS DIAS',
                                $horaActual >= 12 && $horaActual < 19 => 'BUENAS TARDES',
                                default => 'Buenas noches',
                            };
                        @endphp

                        <h4 class="fw-bold mb-1 text-dark">
                            👋 {{ $saludo }}, {{ auth()->user()->persona->nombres }}
                        </h4>

                        <p class="text-muted mb-0 small">
                            Aquí tienes el resumen de tus ventas, horarios y salidas de hoy.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('dashboard.vendedor') }}" class="row g-2 align-items-end">

                        {{-- FECHA --}}
                        <div class="col-12 col-md-auto">
                            <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                                <i class="bi bi-calendar3 me-1"></i>
                                Fecha
                            </label>

                            <input type="date" name="fecha" value="{{ request('fecha', now()->format('Y-m-d')) }}"
                                class="form-control form-control-sm shadow-sm rounded-3">
                        </div>


                        {{-- SUCURSAL --}}
                        <div class="col-12 col-md-auto">
                            <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                                <i class="bi bi-building me-1"></i>
                                Sucursal
                            </label>

                            <input type="text" class="form-control form-control-sm shadow-sm rounded-3"
                                value="{{ auth()->user()->sucursal->nombre_comercial ?? 'Sucursal asignada' }}" readonly>
                        </div>


                        {{-- VENDEDOR --}}
                        <div class="col-12 col-md-auto">
                            <label class="form-label text-uppercase fs-7 fw-bold text-secondary mb-2">
                                <i class="bi bi-person me-1"></i>
                                Vendedor
                            </label>

                            <select name="vendedor_id" class="form-select form-select-sm shadow-sm rounded-3">

                                <option value="">Todos los vendedores</option>

                                @foreach ($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" @selected(request('vendedor_id') == $vendedor->id)>

                                        {{ $vendedor->persona->nombre_completo ?? $vendedor->username }}

                                    </option>
                                @endforeach

                            </select>
                        </div>


                        {{-- BOTÓN --}}
                        <div class="col-12 col-md-auto">

                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm rounded-3">

                                <i class="bi bi-funnel me-1"></i>
                                Filtrar

                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>


        {{-- ===================================================== --}}
        {{-- KPIS --}}
        {{-- ===================================================== --}}
        <div class="row g-3 mb-4">

            {{-- TICKETS --}}
            <div class="col-6 col-md-4 col-xl">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-info border-4">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">

                            <span class="text-muted fs-7 fw-semibold text-uppercase">
                                Tickets hoy
                            </span>

                            <div class="badge bg-info-subtle text-info rounded-circle p-2">
                                <i class="bi bi-ticket-perforated fs-6"></i>
                            </div>

                        </div>

                        <h4 class="fw-bold mb-0 text-dark">
                            {{ number_format($kpis['tickets_hoy'] ?? 0) }}
                        </h4>

                    </div>

                </div>
            </div>


            {{-- VENTAS --}}
            <div class="col-6 col-md-4 col-xl">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-success border-4">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">

                            <span class="text-muted fs-7 fw-semibold text-uppercase">
                                Ventas hoy
                            </span>

                            <div class="badge bg-success-subtle text-success rounded-circle p-2">
                                <i class="bi bi-currency-dollar fs-6"></i>
                            </div>

                        </div>

                        <h4 class="fw-bold mb-0 text-dark">
                            S/ {{ number_format($kpis['ventas_hoy'] ?? 0, 2) }}
                        </h4>

                    </div>

                </div>
            </div>


            {{-- SALIDAS --}}
            <div class="col-6 col-md-4 col-xl">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-primary border-4">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">

                            <span class="text-muted fs-7 fw-semibold text-uppercase">
                                Salidas hoy
                            </span>

                            <div class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                <i class="bi bi-bus-front fs-6"></i>
                            </div>

                        </div>

                        <h4 class="fw-bold mb-0 text-dark">
                            {{ number_format($kpis['salidas_hoy'] ?? 0) }}
                        </h4>

                    </div>

                </div>
            </div>


            {{-- OCUPACIÓN --}}
            <div class="col-6 col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-warning border-4">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">

                            <span class="text-muted fs-7 fw-semibold text-uppercase">
                                Ocupación promedio
                            </span>

                            <div class="badge bg-warning-subtle text-warning rounded-circle p-2">
                                <i class="bi bi-pie-chart fs-6"></i>
                            </div>

                        </div>

                        <h4 class="fw-bold mb-0 text-dark">
                            {{ number_format($kpis['ocupacion_promedio'] ?? 0, 0) }}%
                        </h4>

                    </div>

                </div>
            </div>


            {{-- MIS VENTAS --}}
            <div class="col-12 col-md-6 col-xl">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-kpi border-start border-secondary border-4">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">

                            <span class="text-muted fs-7 fw-semibold text-uppercase">
                                Mis ventas hoy
                            </span>

                            <div class="badge bg-secondary-subtle text-secondary rounded-circle p-2">
                                <i class="bi bi-person-check fs-6"></i>
                            </div>

                        </div>

                        <h4 class="fw-bold mb-0 text-dark">
                            S/ {{ number_format($kpis['mis_ventas_hoy'] ?? 0, 2) }}
                        </h4>

                    </div>

                </div>
            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- GRÁFICO + RANKING --}}
        {{-- ===================================================== --}}
        <div class="row g-3 mb-4">

            {{-- VENTAS POR HORA --}}
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-transparent border-0 pt-4 px-4">

                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-bar-chart-line text-primary me-2"></i>
                            Ventas por hora
                        </h5>

                    </div>

                    <div class="card-body pt-2 px-4 pb-4">

                        <canvas id="ventasPorHoraChart" height="110"></canvas>

                    </div>

                </div>

            </div>


            {{-- RANKING --}}
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">

                    <div class="card-header bg-transparent border-0 pt-4 px-4">

                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-trophy text-primary me-2"></i>
                            Ranking de vendedores
                        </h5>

                    </div>


                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light border-bottom">

                                    <tr>

                                        <th class="ps-4 text-uppercase fs-7 text-secondary">
                                            Vendedor
                                        </th>

                                        <th class="text-center text-uppercase fs-7 text-secondary">
                                            Tickets
                                        </th>

                                        <th class="text-end pe-4 text-uppercase fs-7 text-secondary">
                                            Importe
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($rankingVendedores as $item)
                                        <tr>

                                            <td class="ps-4 fw-semibold text-dark">
                                                {{ $item['nombre'] }}
                                            </td>

                                            <td class="text-center">

                                                <span class="badge bg-light text-dark border">
                                                    {{ $item['tickets'] }}
                                                </span>

                                            </td>

                                            <td class="text-end pe-4 fw-bold text-success">
                                                S/ {{ number_format($item['importe'], 2) }}
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="3" class="text-center text-muted py-4">

                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>

                                                Sin datos disponibles

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- SALIDAS DE LA SUCURSAL --}}
        {{-- ===================================================== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">

            <div
                class="card-header bg-transparent border-0 pt-4 px-4
                d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0 text-dark">

                    <i class="bi bi-bus-front text-primary me-2"></i>
                    Salidas de la sucursal

                </h5>


                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">

                    {{ count($salidas ?? []) }} registros

                </span>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light border-bottom">

                            <tr>

                                <th class="ps-4 text-uppercase fs-7 text-secondary">
                                    Hora
                                </th>

                                <th class="text-uppercase fs-7 text-secondary">
                                    Ruta
                                </th>

                                <th class="text-uppercase fs-7 text-secondary">
                                    Vehículo
                                </th>

                                <th class="text-center text-uppercase fs-7 text-secondary">
                                    Capacidad
                                </th>

                                <th class="text-center text-uppercase fs-7 text-secondary">
                                    Vendidos
                                </th>

                                <th class="text-center text-uppercase fs-7 text-secondary">
                                    Libres
                                </th>

                                <th class="text-center text-uppercase fs-7 text-secondary">
                                    Ocupación
                                </th>

                                <th class="text-center text-uppercase fs-7 text-secondary">
                                    Estado
                                </th>

                                <th class="text-end pe-4 text-uppercase fs-7 text-secondary">
                                    Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($salidas as $salida)
                                @php
                                    $estado = strtolower($salida['estado'] ?? '');

                                    $estadoColor = match ($estado) {
                                        'programado' => 'primary',
                                        'programada' => 'primary',
                                        'en_ruta' => 'warning',
                                        'finalizado' => 'success',
                                        'cancelado' => 'danger',
                                        default => 'secondary',
                                    };

                                    $estadoTexto = match ($estado) {
                                        'programado', 'programada' => 'Programado',
                                        'en_ruta' => 'En ruta',
                                        'finalizado' => 'Finalizado',
                                        'cancelado' => 'Cancelado',
                                        default => ucfirst(str_replace('_', ' ', $estado)),
                                    };
                                @endphp


                                <tr>

                                    <td class="ps-4 fw-medium">
                                        {{ $salida['hora'] }}
                                    </td>


                                    <td class="fw-semibold text-dark">
                                        {{ $salida['ruta'] }}
                                    </td>


                                    <td>

                                        <span class="badge bg-light text-secondary border">
                                            {{ $salida['vehiculo'] }}
                                        </span>

                                    </td>


                                    <td class="text-center">
                                        {{ $salida['capacidad'] }}
                                    </td>


                                    <td class="text-center fw-semibold">
                                        {{ $salida['vendidos'] }}
                                    </td>


                                    <td class="text-center">
                                        {{ $salida['libres'] }}
                                    </td>


                                    <td class="text-center">

                                        <span
                                            class="badge bg-{{ $salida['ocupacion_color'] ?? 'secondary' }}-subtle
                                            text-{{ $salida['ocupacion_color'] ?? 'secondary' }}
                                            rounded-pill px-3 py-2">

                                            {{ $salida['ocupacion'] }}%

                                        </span>

                                    </td>


                                    <td class="text-center">

                                        <span
                                            class="badge bg-{{ $estadoColor }}-subtle
                                            text-{{ $estadoColor }}
                                            rounded-pill px-3 py-2">

                                            {{ $estadoTexto }}

                                        </span>

                                    </td>


                                    <td class="text-end pe-4">

                                        <a href="{{ $salida['url_detalle'] }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3">

                                            <i class="bi bi-eye me-1"></i>
                                            Ver

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="9" class="text-center text-muted py-5">

                                        <i class="bi bi-folder-x fs-2 d-block mb-2"></i>

                                        No hay salidas registradas.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- ALERTAS + RESUMEN HORARIOS --}}
        {{-- ===================================================== --}}
        <div class="row g-3">

            {{-- ALERTAS --}}
            <div class="col-lg-6">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-header bg-transparent border-0 pt-4 px-4">

                        <h5 class="fw-bold mb-0 text-dark">

                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            Alertas rápidas

                        </h5>

                    </div>


                    <div class="card-body px-4 pb-4">

                        <ul class="list-group list-group-flush">

                            @forelse($alertas as $alerta)
                                <li class="list-group-item px-0 bg-transparent">

                                    <div class="d-flex align-items-start gap-2">

                                        <i class="bi bi-exclamation-circle text-warning mt-1"></i>

                                        <span>
                                            {{ $alerta }}
                                        </span>

                                    </div>

                                </li>

                            @empty

                                <li class="list-group-item px-0 bg-transparent text-muted">

                                    <i class="bi bi-check-circle text-success me-2"></i>

                                    No hay alertas por mostrar.

                                </li>
                            @endforelse

                        </ul>

                    </div>

                </div>

            </div>


            {{-- RESUMEN HORARIO --}}
            <div class="col-lg-6">

                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">

                    <div class="card-header bg-transparent border-0 pt-4 px-4">

                        <h5 class="fw-bold mb-0 text-dark">

                            <i class="bi bi-clock-history text-primary me-2"></i>
                            Resumen por horario

                        </h5>

                    </div>


                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light border-bottom">

                                    <tr>

                                        <th class="ps-4 text-uppercase fs-7 text-secondary">
                                            Horario
                                        </th>

                                        <th class="text-center text-uppercase fs-7 text-secondary">
                                            Tickets
                                        </th>

                                        <th class="text-end pe-4 text-uppercase fs-7 text-secondary">
                                            Ventas
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse($resumenHorarios as $item)
                                        <tr>

                                            <td class="ps-4 fw-semibold text-dark">
                                                {{ $item['horario'] }}
                                            </td>


                                            <td class="text-center">

                                                <span class="badge bg-light text-dark border">

                                                    {{ $item['tickets'] }}

                                                </span>

                                            </td>


                                            <td class="text-end pe-4 fw-bold text-success">

                                                S/ {{ number_format($item['ventas'], 2) }}

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="3" class="text-center text-muted py-4">

                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>

                                                Sin datos disponibles

                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ventasPorHoraChart =
            @json($ventasPorHoraChart ?? ['labels' => [], 'data' => []]);

        const ctxVentasPorHora =
            document.getElementById('ventasPorHoraChart');

        if (ctxVentasPorHora) {

            new Chart(ctxVentasPorHora, {

                type: 'bar',

                data: {

                    labels: ventasPorHoraChart.labels,

                    datasets: [{
                        label: 'Ventas por hora',
                        data: ventasPorHoraChart.data,

                        backgroundColor: '#0d6efd',

                        borderRadius: 6,

                        maxBarThickness: 40
                    }]

                },

                options: {

                    responsive: true,

                    plugins: {

                        legend: {
                            display: false
                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            grid: {
                                borderDash: [5, 5]
                            }

                        },

                        x: {

                            grid: {
                                display: false
                            }

                        }

                    }

                }

            });

        }
    </script>
@endpush


@push('styles')
    <style>
        .fs-7 {
            font-size: 0.75rem;
        }

        .card-kpi {
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .card-kpi:hover {
            transform: translateY(-3px);

            box-shadow:
                0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .table> :not(caption)>*>* {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }

        .table-light th {
            white-space: nowrap;
        }
    </style>
@endpush
