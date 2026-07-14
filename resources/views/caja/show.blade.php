@extends('layouts.app')

@section('content')
    <div class="container py-3">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2 px-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                    <div>
                        <h5 class="mb-0">Caja #{{ $caja->id }}</h5>
                        <small class="text-muted">
                            {{ $caja->usuario->persona->nombre_completo ?? '---' }}
                            <span class="mx-1">|</span>
                            {{ $caja->sucursal->nombre_comercial ?? '---' }}
                        </small>
                    </div>

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
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalIngreso">
                                <i data-lucide="banknote-arrow-up"></i> Registrar ingreso
                            </button>

                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalSalida">
                                <i data-lucide="banknote-arrow-down"></i> Registrar egreso
                            </button>


                            <form action="{{ route('caja.cerrar', $caja->id) }}" method="POST"
                                class="cerrar-caja-form d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm px-2">
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

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-danger shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Egresos</strong></h6>
                        <div id="total_egresos" class="fw-bold text-danger">S/ {{ number_format($caja->total_salidas, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Ingresos</strong></h6>
                        <div id="total_ingresos" class="fw-bold text-primary">S/
                            {{ number_format($caja->total_ingresos, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <h6 class="text-muted d-block mb-1"><strong>Efectivo esperado</strong></h6>
                        <div id="efectivo_esperado" class="fw-bold text-success">S/
                            {{ number_format($caja->efectivo_esperado, 2) }}</div>
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

        <div id="contenedor-tabla-movimientos">
            @include('caja.partials.tabla_movimientos', [
                'caja' => $caja,
                'detalles' => $detalles,
            ]) </div>

    </div>
    @include('caja.modals.egreso')
    @include('caja.modals.ingreso')
@endsection
@push('scripts')
    <script src="{{ asset('js/caja_detalle.js') }}"></script>
@endpush
