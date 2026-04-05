@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Caja #{{ $caja->id }}</h2>
                <p class="text-muted mb-0">
                    Cajero: <strong>{{ $caja->usuario->persona->nombre_completo ?? '---' }}</strong> |
                    Sucursal: <strong>{{ $caja->sucursal->nombre_comercial ?? '---' }}</strong>
                </p>
            </div>

            <div class="mt-2 mt-md-0">
                <a href="{{ route('caja.print_corte', $caja->id) }}" target="_blank" class="btn btn-dark">
                    Imprimir corte
                </a>

                @if (!in_array($caja->estado, ['C', 'cerrada']))
                    <form action="{{ route('caja.cerrar', $caja->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('¿Seguro que deseas cerrar esta caja?')">
                            Cerrar caja
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Resumen por método de pago --}}
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ingreso por Yape</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->ingresos_yape, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ingreso por Transferencia</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->ingresos_transferencia, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ingreso por Tarjeta</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->ingresos_tarjeta, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ingreso por Efectivo</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->ingresos_efectivo, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen general --}}
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Monto apertura</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->monto_apertura, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Total ingresos</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->total_ingresos, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Total egresos</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->total_salidas, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Saldo sistema</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->monto_actual, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen de caja física --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <small class="text-muted">Egresos efectivo</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->egresos_efectivo, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <small class="text-muted">Efectivo esperado</small>
                        <h4 class="mt-2 mb-0">S/ {{ number_format($caja->efectivo_esperado, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-secondary h-100">
                    <div class="card-body">
                        <small class="text-muted">Estado</small>
                        <h4 class="mt-2 mb-0">
                            @if (in_array($caja->estado, ['C', 'cerrada']))
                                <span class="text-danger">CERRADA</span>
                            @else
                                <span class="text-success">ABIERTA</span>
                            @endif
                        </h4>
                        <small class="text-muted d-block mt-2">
                            Apertura: {{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') }}
                        </small>
                        <small class="text-muted d-block">
                            Cierre: {{ optional($caja->fecha_cierre)->format('d/m/Y h:i A') ?? '---' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        @if (!in_array($caja->estado, ['C', 'cerrada']))
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <strong>Registrar ingreso</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('caja.ingreso', $caja->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Subtipo</label>
                                    <select name="subtipo_movimiento_caja_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($subtiposIngreso as $subtipo)
                                            <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Método de pago</label>
                                    <select name="metodo_pago_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($metodosPago as $metodo)
                                            <option value="{{ $metodo->id }}">{{ $metodo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Monto</label>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    Registrar ingreso
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <strong>Registrar egreso</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('caja.salida', $caja->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Subtipo</label>
                                    <select name="subtipo_movimiento_caja_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($subtiposSalida as $subtipo)
                                            <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Método de pago</label>
                                    <select name="metodo_pago_id" class="form-select" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($metodosPago as $metodo)
                                            <option value="{{ $metodo->id }}">{{ $metodo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Monto</label>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger w-100">
                                    Registrar egreso
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Movimientos de caja</strong>
                <span class="text-muted">Total: {{ $caja->detalles->count() }}</span>
            </div>

            <div class="card-body">
                @if ($caja->detalles->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Ticket</th>
                                    <th>Tipo</th>
                                    <th>Subtipo</th>
                                    <th>Método</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($caja->detalles as $detalle)
                                    <tr class="{{ $detalle->anulado ? 'table-danger' : '' }}">
                                        <td>{{ $detalle->created_at?->format('d/m/Y h:i A') }}</td>
                                        <td>{{ $detalle->numero_ticket }}</td>
                                        <td>
                                            @if ($detalle->amount > 0)
                                                <span class="badge bg-success">Ingreso</span>
                                            @else
                                                <span class="badge bg-danger">Egreso</span>
                                            @endif
                                        </td>
                                        <td>{{ $detalle->subtipo->descripcion ?? '---' }}</td>
                                        <td>{{ $detalle->metodoPago->descripcion ?? '---' }}</td>
                                        <td>{{ $detalle->description ?? '---' }}</td>
                                        <td>
                                            <strong>
                                                S/ {{ number_format(abs($detalle->amount), 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if ($detalle->anulado)
                                                <span class="badge bg-secondary">Anulado</span>
                                            @else
                                                <span class="badge bg-primary">Activo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('caja.reimprimir', $detalle->id) }}" target="_blank"
                                                class="btn btn-sm btn-dark">
                                                Reimprimir
                                            </a>

                                            @if (!$detalle->anulado && !in_array($caja->estado, ['C', 'cerrada']))
                                                <form action="{{ route('caja.anular', $detalle->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('¿Seguro que deseas anular este ticket?')">
                                                        Anular
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="mb-0 text-muted">No hay movimientos registrados en esta caja.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
