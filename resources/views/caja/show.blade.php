@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- Cabecera --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2 px-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                    {{-- Info --}}
                    <div>
                        <h5 class="mb-0">Caja #{{ $caja->id }}</h5>
                        <small class="text-muted">
                            {{ $caja->usuario->persona->nombre_completo ?? '---' }}
                            <span class="mx-1">|</span>
                            {{ $caja->sucursal->nombre_comercial ?? '---' }}
                        </small>
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex flex-wrap gap-1">

                        @if (auth()->user()->hasAnyRole(['Administrador', 'Super Administrador']))
                            <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary btn-sm px-2">
                                ← Volver
                            </a>
                        @endif

                        <a href="{{ route('caja.print_corte', $caja->id) }}" target="_blank"
                            class="btn btn-dark btn-sm px-2">
                            Corte
                        </a>

                        @if (!in_array($caja->estado, ['C', 'cerrada']))
                            <form action="{{ route('caja.cerrar', $caja->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm px-2"
                                    onclick="return confirm('¿Cerrar caja?')">
                                    Cerrar
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif

        {{-- Resumen general --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-danger shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Egresos</strong></h6>
                        <div class="fw-bold text-danger">S/ {{ number_format($caja->total_salidas, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Ingresos</strong></h6>
                        <div class="fw-bold text-primary">S/ {{ number_format($caja->total_ingresos, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Efectivo esperado</strong></h6>
                        <div class="fw-bold text-success">S/ {{ number_format($caja->efectivo_esperado, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-secondary shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block mt-1">
                            Apertura: {{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') }}
                        </small>
                        <small class="text-muted d-block">
                            Cierre:
                            {{ $caja->fecha_cierre ? optional($caja->fecha_cierre)->format('d/m/Y h:i A') : '---' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>


        {{-- Resumen rápido --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Yape</small>
                        <div class="fw-bold">S/ {{ number_format($caja->ingresos_yape, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Plin</small>
                        <div class="fw-bold">S/ {{ number_format($caja->ingresos_plin ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Transferencia</small>
                        <div class="fw-bold">S/ {{ number_format($caja->ingresos_transferencia, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Tarjeta</small>
                        <div class="fw-bold">S/ {{ number_format($caja->ingresos_tarjeta, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Efectivo</small>
                        <div class="fw-bold">S/ {{ number_format($caja->ingresos_efectivo, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <small class="text-muted d-block">Apertura</small>
                        <div class="fw-bold">S/ {{ number_format($caja->monto_apertura, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if (!in_array($caja->estado, ['C', 'cerrada']))
            <div class="row g-2 mb-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-2">
                            <strong class="small">Registrar ingreso</strong>
                        </div>
                        <div class="card-body py-2">
                            <form id="form-ingreso" action="{{ route('caja.ingreso', $caja->id) }}" method="POST"
                                class="row g-2">
                                @csrf

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Subtipo</label>
                                    <select name="subtipo_movimiento_caja_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($subtiposIngreso as $subtipo)
                                            <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Tipo de ingreso</label>
                                    <select name="metodo_pago_id" id="tipo_ingreso" class="form-select form-select-sm"
                                        required>
                                        <option value="">Seleccione</option>
                                        @foreach ($metodosPago as $metodo)
                                            <option value="{{ $metodo->id }}">{{ $metodo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- MONTO SIMPLE --}}
                                <div class="col-md-4 ingreso-campo d-none" id="ingreso_monto_simple">
                                    <label class="form-label small mb-1">Monto</label>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        class="form-control form-control-sm">
                                </div>

                                {{-- MIXTO --}}
                                <div class="col-md-4 ingreso-campo d-none" id="ingreso_monto_efectivo">
                                    <label class="form-label small mb-1">Monto efectivo</label>
                                    <input type="number" step="0.01" min="0.01" name="monto_efectivo"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-4 ingreso-campo d-none" id="ingreso_monto_digital">
                                    <label class="form-label small mb-1">Monto digital</label>
                                    <input type="number" step="0.01" min="0.01" name="monto_digital"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-4 ingreso-campo d-none" id="ingreso_billetera">
                                    <label class="form-label small mb-1">Billetera</label>
                                    <select name="billetera_digital_id" class="form-select form-select-sm">
                                        <option value="">Seleccione</option>
                                        @foreach ($billeterasDigitales as $billetera)
                                            <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small mb-1">Descripción</label>
                                    <input type="text" name="description" class="form-control form-control-sm">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        Registrar ingreso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-2">
                            <strong class="small">Registrar egreso</strong>
                        </div>
                        <div class="card-body py-2">
                            <form action="{{ route('caja.salida', $caja->id) }}" method="POST" class="row g-2">
                                @csrf

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Subtipo</label>
                                    <select name="subtipo_movimiento_caja_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($subtiposSalida as $subtipo)
                                            <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Tipo de salida</label>
                                    <select name="metodo_pago_id" id="tipo_salida" class="form-select form-select-sm"
                                        required>
                                        <option value="">Seleccione</option>
                                        @foreach ($metodosPago as $metodo)
                                            <option value="{{ $metodo->id }}">{{ $metodo->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- EFECTIVO --}}
                                <div class="col-md-4 salida-campo d-none" id="campo_monto_efectivo">
                                    <label class="form-label small mb-1">Monto efectivo</label>
                                    <input type="number" step="0.01" min="0.01" name="monto_efectivo"
                                        class="form-control form-control-sm">
                                </div>

                                {{-- DIGITAL --}}
                                <div class="col-md-4 salida-campo d-none" id="campo_monto_digital">
                                    <label class="form-label small mb-1">Monto digital</label>
                                    <input type="number" step="0.01" min="0.01" name="monto_digital"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-4 salida-campo d-none" id="campo_billetera">
                                    <label class="form-label small mb-1">Billetera</label>
                                    <select name="billetera_digital_id" class="form-select form-select-sm">
                                        <option value="">Seleccione</option>
                                        @foreach ($billeterasDigitales as $billetera)
                                            <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 salida-campo d-none" id="campo_monto_simple">
                                    <label class="form-label small mb-1">Monto</label>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        class="form-control form-control-sm">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small mb-1">Descripción</label>
                                    <input type="text" name="description" class="form-control form-control-sm">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        Registrar egreso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Movimientos --}}
        <div id="contenedor-tabla-movimientos">
            @include('caja.partials.tabla_movimientos', ['caja' => $caja])
        </div>

    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/caja_detalle.js') }}"></script>
@endpush
