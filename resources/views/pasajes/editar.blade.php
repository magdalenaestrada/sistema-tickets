@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row mt-3">
            <form method="POST" enctype="multipart/form-data" id="formVenta">
                @csrf

                @if (isset($pasaje))
                    <input type="hidden" name="pasaje_id" value="{{ $pasaje->id }}">
                @endif

                <div class="row">
                    <div class="col-md-9 mb-3">
                        @foreach ($asientos as $index => $asiento)
                            <div class="card shadow-sm mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Asiento {{ $asiento }}</strong>

                                    <button class="btn btn-sm btn-primary btn-cambio-horario"
                                        data-index="{{ $index }}" data-asiento="{{ $asiento }}"
                                        data-horario="{{ $horario->id }}" data-bs-toggle="modal"
                                        data-bs-target="#modalCambioHorario">
                                        Cambiar asiento / horario
                                    </button>

                                </div>

                                <div class="card-body">
                                    <input type="hidden" name="asientos[]" value="{{ $asiento }}">
                                    <input type="hidden" name="horario_id[]" value="{{ $horario->id }}">

                                    <div class="row">
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Tipo</label>
                                            <select class="form-select" name="tipo_documento_id[]"
                                                id="tipo_documento_id_{{ $index }}" required>
                                                @foreach ($tipos_documentos as $tipo_documento)
                                                    <option value="{{ $tipo_documento->id }}"
                                                        @if (isset($pasaje) && $pasaje->persona->tipo_documento_id == $tipo_documento->id) selected @endif>
                                                        {{ $tipo_documento->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Documento</label>
                                            <input type="text" class="form-control" id="documento_{{ $index }}"
                                                name="documento[]" required
                                                value="{{ $pasaje->persona->documento ?? '' }}">
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Nombres</label>
                                            <input type="text" class="form-control" id="nombres_{{ $index }}"
                                                name="nombres[]" required value="{{ $pasaje->persona->nombres ?? '' }}">
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Apellidos</label>
                                            <input type="text" class="form-control" id="apellidos_{{ $index }}"
                                                name="apellidos[]" required
                                                value="{{ $pasaje->persona->apellidos ?? '' }}">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Celular</label>
                                            <input type="text" class="form-control" id="celular_{{ $index }}"
                                                name="celular[]" required value="{{ $pasaje->persona->celular ?? '' }}">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono_{{ $index }}"
                                                name="telefono[]" value="{{ $pasaje->persona->telefono ?? '' }}">
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Correo electrónico</label>
                                            <input type="email" class="form-control" id="correo_{{ $index }}"
                                                name="direccion[]" value="{{ $pasaje->persona->correo ?? '' }}">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Descuento</label>
                                            <input type="number" step="0.01" class="form-control"
                                                id="descuento_{{ $index }}" name="descuento[]"
                                                value="{{ $pasaje->descuento ?? 0 }}">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-check">
                                            <input type="checkbox" class="form-check-input"
                                                id="pasajero_menor_{{ $index }}"
                                                name="pasajero_menor[{{ $index }}]" value="1"
                                                @if (isset($pasaje) && $pasaje->pasajero_menor) checked @endif>
                                            <label class="form-check-label" for="pasajero_menor_{{ $index }}">
                                                ¿Pasajero menor de edad?
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Autorización PDF</label>
                                            <input type="file" accept=".pdf" class="form-control"
                                                id="autorizacion_pdf_{{ $index }}" name="autorizacion_pdf[]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3"><strong>ASIENTOS: {{ implode(', ', $asientos) }}</strong></h6>
                                <p class="mb-1"><strong>Origen:</strong> {{ $horario->punto_origen->nombre_comercial }}
                                </p>
                                <p class="mb-1"><strong>Destino:</strong>
                                    {{ $horario->punto_destino->nombre_comercial }}
                                </p>
                                <p class="mb-1"><strong>Vehículo:</strong> {{ $horario->tipo_vehiculo->descripcion }}
                                </p>
                                <p class="mb-1"><strong>Fecha:</strong>
                                    {{ optional($horario->fechas->first())->fecha_salida ? $horario->fechas->first()->fecha_salida->format('Y-m-d') : '' }}
                                </p>
                                <p class="mb-0"><strong>Hora:</strong> {{ $horario->hora_salida }}</p>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>Facturación</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de documento</label>
                                    <select name="tipo_documento_factura_id" id="tipo_documento_factura_id"
                                        class="form-select">
                                        @foreach ($tipos_documentos_facturas as $tipo_documento_factura)
                                            <option value="{{ $tipo_documento_factura->id }}"
                                                @if (isset($pasaje->venta) && $pasaje->venta->tipo_documento_factura_id == $tipo_documento_factura->id) selected @endif>
                                                {{ $tipo_documento_factura->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Número documento</label>
                                    <input type="text" id="numero_documento_id" name="numero_documento_id"
                                        class="form-control" value="{{ $pasaje->venta->persona->documento ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Razón social</label>
                                    <input type="text" id="razon_social" name="razon_social" class="form-control"
                                        value="{{ $pasaje->venta->persona ? $pasaje->venta->persona->razon_social ?? $pasaje->venta->persona->nombres . ' ' . $pasaje->venta->persona->apellidos : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>Método de Pago</strong>
                            </div>
                            <div class="card-body">

                                @php
                                    $precioBoleto = $horario->costo_base ?? 0;
                                    $costoTotal = 0;
                                    $pagoEfectivo = 0;
                                    $pagoBilletera = 0;
                                    $billeteraId = null;
                                    $metodoPagoId = null;

                                    if (isset($pasaje->venta)) {
                                        // Obtener el total de la venta
                                        $costoTotal = $pasaje->venta->total;

                                        // Obtener los pagos
                                        $pagos = $pasaje->venta->pagos;

                                        if ($pagos->isNotEmpty()) {
                                            // Obtener el método de pago del primer registro
                                            $metodoPagoId = $pagos->first()->metodo_pago_id;

                                            // Sumar pagos por método
                                            foreach ($pagos as $pago) {
                                                if ($pago->metodo_pago_id == 1) {
                                                    // Efectivo
                                                    $pagoEfectivo += $pago->total;
                                                } elseif ($pago->metodo_pago_id == 2) {
                                                    // Digital
                                                    $pagoBilletera += $pago->total;
                                                    if ($pago->billetera_id) {
                                                        $billeteraId = $pago->billetera_id;
                                                    }
                                                } elseif ($pago->metodo_pago_id == 3) {
                                                    // Mixto
                                                    // En método mixto, separar por si tiene billetera_id
                                                    if ($pago->billetera_id) {
                                                        $pagoBilletera += $pago->total;
                                                        $billeteraId = $pago->billetera_id;
                                                    } else {
                                                        $pagoEfectivo += $pago->total;
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        // Modo creación
                                        $costoTotal = $precioBoleto * count($asientos);
                                    }
                                @endphp

                                <div class="mb-3">
                                    <label class="form-label">Método</label>
                                    <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                        @foreach ($metodos_pago as $metodo_pago)
                                            <option value="{{ $metodo_pago->id }}"
                                                @if ($metodoPagoId == $metodo_pago->id) selected @endif>
                                                {{ $metodo_pago->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 grupo_costo_total" hidden>
                                    <label class="form-label">Costo total</label>
                                    <input type="number" step="0.01" id="costo_total" name="costo_total"
                                        class="form-control" readonly
                                        value="{{ number_format($costoTotal, 2, '.', '') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pago efectivo</label>
                                    <input type="number" step="0.01" id="pago_efectivo" name="pago_efectivo"
                                        class="form-control" value="{{ number_format($pagoEfectivo, 2, '.', '') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Yape/Plin/POS</label>
                                    <select name="billetera_id" id="billetera_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($billeteras_digitales as $billetera)
                                            <option value="{{ $billetera->id }}"
                                                @if ($billeteraId == $billetera->id) selected @endif>
                                                {{ $billetera->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pago digital</label>
                                    <input type="number" step="0.01" id="pago_billetera" name="pago_billetera"
                                        class="form-control" value="{{ number_format($pagoBilletera, 2, '.', '') }}">
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    @if (isset($pasaje))
                                        <button type="button" class="btn btn-success w-100" id="btnActualizarPasaje">
                                            <i class="bi bi-check-circle"></i> Actualizar pasaje
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-warning w-100 mb-2" id="btnReservar">
                                            <i class="bi bi-bookmark"></i> Reservar
                                        </button>

                                        <button type="button" class="btn btn-primary w-100" id="btnTerminarVenta">
                                            <i class="bi bi-cash-coin"></i> Terminar venta
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
        @include('pasajes.modals.cambio')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/cambiar_horario.js') }}"></script>
@endpush
