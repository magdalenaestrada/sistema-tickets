@extends('layouts.app')

@section('title', 'Dashboard Vendedor')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard de ventas</h2>
                <p class="text-muted mb-0">Resumen operativo de ventas, horarios y salidas de tu sucursal.</p>
            </div>

            <form method="GET" action="{{ route('dashboard.vendedor') }}" class="row g-2">
                <div class="col-auto">
                    <input type="date" name="fecha" value="{{ request('fecha', now()->format('Y-m-d')) }}"
                        class="form-control">
                </div>
                <div class="col-auto">
                    <input type="text" class="form-control"
                        value="{{ auth()->user()->sucursal->nombre_comercial ?? 'Sucursal asignada' }}" readonly>
                </div>
                <div class="col-auto">
                    <select name="vendedor_id" class="form-select">
                        <option value="">Todos los vendedores</option>
                        @foreach ($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}" @selected(request('vendedor_id') == $vendedor->id)>
                                {{ $vendedor->persona->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary px-4">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Tickets hoy</span>
                        <h3 class="fw-bold mb-0">{{ $kpis['tickets_hoy'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Ventas hoy</span>
                        <h3 class="fw-bold mb-0">S/ {{ number_format($kpis['ventas_hoy'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Salidas hoy</span>
                        <h3 class="fw-bold mb-0">{{ $kpis['salidas_hoy'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Ocupación promedio</span>
                        <h3 class="fw-bold mb-0">{{ $kpis['ocupacion_promedio'] ?? 0 }}%</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <span class="text-muted small d-block mb-2">Mis ventas hoy</span>
                        <h3 class="fw-bold mb-0">S/ {{ number_format($kpis['mis_ventas_hoy'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Ventas por hora</h5>
                    </div>
                    <div class="card-body pt-2 px-4 pb-4">
                        <canvas id="ventasPorHoraChart" height="110"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Ranking de vendedores</h5>
                    </div>
                    <div class="card-body pt-2 px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Vendedor</th>
                                        <th class="text-center">Tickets</th>
                                        <th class="text-end pe-4">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingVendedores as $item)
                                        <tr>
                                            <td class="ps-4">{{ $item['nombre'] }}</td>
                                            <td class="text-center">{{ $item['tickets'] }}</td>
                                            <td class="text-end pe-4">S/ {{ number_format($item['importe'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin datos disponibles
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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-semibold mb-0">Salidas de la sucursal</h5>
                <span class="badge text-bg-light rounded-pill px-3 py-2">{{ count($salidas ?? []) }} registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Hora</th>
                                <th>Ruta</th>
                                <th>Vehículo</th>
                                <th class="text-center">Capacidad</th>
                                <th class="text-center">Vendidos</th>
                                <th class="text-center">Libres</th>
                                <th class="text-center">% Ocupación</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salidas as $salida)
                                <tr>
                                    <td class="ps-4">{{ $salida['hora'] }}</td>
                                    <td>{{ $salida['ruta'] }}</td>
                                    <td>{{ $salida['vehiculo'] }}</td>
                                    <td class="text-center">{{ $salida['capacidad'] }}</td>
                                    <td class="text-center">{{ $salida['vendidos'] }}</td>
                                    <td class="text-center">{{ $salida['libres'] }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $salida['ocupacion_color'] ?? 'secondary' }} rounded-pill px-3 py-2">
                                            {{ $salida['ocupacion'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge text-bg-light rounded-pill px-3 py-2">{{ $salida['estado'] }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ $salida['url_detalle'] }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">No hay salidas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Alertas rápidas</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <ul class="list-group list-group-flush">
                            @forelse($alertas as $alerta)
                                <li class="list-group-item px-0">{{ $alerta }}</li>
                            @empty
                                <li class="list-group-item px-0 text-muted">No hay alertas por mostrar.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-semibold mb-0">Resumen por horario</h5>
                    </div>
                    <div class="card-body pt-2 px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Horario</th>
                                        <th class="text-center">Tickets</th>
                                        <th class="text-end pe-4">Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resumenHorarios as $item)
                                        <tr>
                                            <td class="ps-4">{{ $item['horario'] }}</td>
                                            <td class="text-center">{{ $item['tickets'] }}</td>
                                            <td class="text-end pe-4">S/ {{ number_format($item['ventas'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin datos disponibles
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
        const ventasPorHoraChart = @json($ventasPorHoraChart ?? ['labels' => [], 'data' => []]);

        const ctxVentasPorHora = document.getElementById('ventasPorHoraChart');
        if (ctxVentasPorHora) {
            new Chart(ctxVentasPorHora, {
                type: 'bar',
                data: {
                    labels: ventasPorHoraChart.labels,
                    datasets: [{
                        label: 'Ventas por hora',
                        data: ventasPorHoraChart.data,
                        borderWidth: 1,
                        borderRadius: 10,
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .card {
            transition: .2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .table> :not(caption)>*>* {
            padding-top: 0.95rem;
            padding-bottom: 0.95rem;
            vertical-align: middle;
        }

        .table-light th {
            font-size: .84rem;
            font-weight: 700;
            color: #4b5563;
            white-space: nowrap;
        }

        .card-header h5 {
            color: #111827;
        }

        .badge {
            font-weight: 600;
        }
    </style>
@endpush
