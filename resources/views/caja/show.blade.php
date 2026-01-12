@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detalle de la caja {{ $caja->sucursal->nombre_comercial }}</h5>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-start border-primary border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i data-lucide="wallet" class="me-3 text-primary"></i>
                                    <div>
                                        <div class="text-muted small">Monto apertura</div>
                                        <h5 class="mb-0">
                                            {{ number_format($caja->monto_apertura, 2) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-start border-success border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i data-lucide="trending-up" class="me-3 text-success"></i>
                                    <div>
                                        <div class="text-muted small">Monto actual</div>
                                        <h5 class="mb-0">
                                            {{ number_format($montoActual, 2) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-start border-info border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i data-lucide="arrow-down-circle" class="me-3 text-info"></i>
                                    <div>
                                        <div class="text-muted small">Total ingresos</div>
                                        <h5 class="mb-0">
                                            {{ number_format($totalIngresos, 2) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-start border-danger border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i data-lucide="arrow-up-circle" class="me-3 text-danger"></i>
                                    <div>
                                        <div class="text-muted small">Total salidas</div>
                                        <h5 class="mb-0">
                                            {{ number_format(abs($totalSalidas), 2) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Botones de registro --}}
            <div class="mb-4">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ingresoModal">Registrar
                    Ingreso</button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#salidaModal">Registrar
                    Salida</button>
            </div>

            {{-- Tabla de movimientos --}}
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Movimiento</th>
                        <th>Descripción</th>
                        <th>Tipo servicio</th>
                        <th>Monto</th>
                        <th>Método pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($caja->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->created_at->format('d/m/Y') }}</td>
                            <td>{{ $detalle->created_at->format('H:i:s') }}</td>
                            <td>{{ $detalle->subtipo->tipo_movimiento->descripcion ?? 'N/A' }}</td>
                            <td>{{ $detalle->description }}</td>
                            <td>
                                @if ($detalle->servicio)
                                    {{ class_basename($detalle->servicio) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format(abs($detalle->amount), 2) }}</td>
                            <td>{{ $detalle->metodoPago->descripcion }}</td>
                            <td>
                                <a href="{{ route('caja.ticket.reimprimir', $detalle) }}" target="_blank"
                                    class="btn btn-sm btn-info">
                                    Reimprimir ticket
                                </a>
                                @if (!$detalle->anulado)
                                    <form action="{{ route('caja.ticket.anular', $detalle) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Seguro de anular?')">
                                            Anular ticket
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary">Anulado</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modales para ingreso y salida --}}
        @include('caja.modals.ingreso')
        @include('caja.modals.salida')
    @endsection
