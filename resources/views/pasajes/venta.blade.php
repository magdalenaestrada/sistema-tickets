@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row mt-3">
            <form method="POST" enctype="multipart/form-data" id="formVenta">
                @csrf

                <input type="hidden" name="salida_id" value="{{ $salida->id }}">
                <input type="hidden" name="origen_id" value="{{ $origen->id }}">
                <input type="hidden" name="destino_id" value="{{ $destino->id }}">

                <div class="row">
                    <div class="col-md-9 mb-3">
                        @foreach ($asientos as $index => $asiento)
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    <strong>Asiento {{ $asiento }}</strong>
                                </div>

                                <div class="card-body">
                                    <input type="hidden" name="asientos[]" value="{{ $asiento }}">

                                    <div class="row">
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_documento_id[]"
                                                id="tipo_documento_id_{{ $index }}" required>
                                                @foreach ($tipos_documentos as $tipo_documento)
                                                    <option value="{{ $tipo_documento->id }}">
                                                        {{ $tipo_documento->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Documento <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control solo-numeros"
                                                id="documento_{{ $index }}" data-index="{{ $index }}"
                                                name="documento[]" required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control solo-letras"
                                                id="nombres_{{ $index }}" name="nombres[]" required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control solo-letras"
                                                id="apellidos_{{ $index }}" name="apellidos[]" required>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Celular </label>
                                            <input type="text" class="form-control solo-numeros"
                                                id="celular_{{ $index }}" name="celular[]" maxlength="9">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control solo-numeros"
                                                id="telefono_{{ $index }}" name="telefono[]" maxlength="9">
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Correo</label>
                                            <input type="email" class="form-control" id="correo_{{ $index }}"
                                                name="correo[]">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Descuento</label>
                                            <input type="text" class="form-control descuento-input"
                                                data-index="{{ $index }}" id="descuento_{{ $index }}"
                                                name="descuento_codigo[]" placeholder="Código">
                                            <small class="text-muted" id="descuento_msg_{{ $index }}"></small>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-check">
                                            <input type="checkbox" class="form-check-input pasajero-menor-check"
                                                id="pasajero_menor_{{ $index }}" data-index="{{ $index }}"
                                                name="pasajero_menor[{{ $index }}]" value="1">
                                            <label class="form-check-label" for="pasajero_menor_{{ $index }}">
                                                ¿Pasajero menor de edad?
                                            </label>
                                        </div>

                                        <div class="col-md-6 autorizacion-container"
                                            id="autorizacion_container_{{ $index }}" style="display:none;">
                                            <label class="form-label">Autorización PDF <span
                                                    class="text-danger">*</span></label>
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
                                <p class="mb-1"><strong>Precio C/U:</strong> <span
                                        id="precio_unitario">{{ number_format($precioUnitario, 2) }}</span></p>
                                <p class="mb-1"><strong>Origen:</strong> {{ $origen->nombre_comercial }}</p>
                                <p class="mb-1"><strong>Destino:</strong> {{ $destino->nombre_comercial }}</p>
                                <p class="mb-1"><strong>Vehículo:</strong>
                                    {{ $salida->horario->tipo_vehiculo->descripcion }}</p>
                                <p class="mb-1"><strong>Fecha:</strong> {{ $salida->fecha_salida->format('Y-m-d') }}</p>
                                <p class="mb-0"><strong>Hora:</strong> {{ $salida->horario->hora_formateada }}</p>
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
                                        <option value="">Seleccionar tipo</option>
                                        @foreach ($tipos_documentos_facturas as $tipo_documento_factura)
                                            <option value="{{ $tipo_documento_factura->id }}">
                                                {{ $tipo_documento_factura->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Número documento</label>
                                    <input type="text" id="numero_documento_id" name="numero_documento_id"
                                        class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Razón social</label>
                                    <input type="text" id="razon_social" name="razon_social" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>Método de Pago</strong>
                            </div>

                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Método</label>
                                    <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                        <option value="1">Efectivo</option>
                                        <option value="2">Digital</option>
                                        <option value="3">Mixto</option>
                                    </select>
                                </div>

                                <div class="mb-3 grupo_costo_total" hidden>
                                    <label class="form-label">Costo total</label>
                                    <input type="number" step="0.01" id="costo_total" name="costo_total"
                                        class="form-control" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pago efectivo</label>
                                    <input type="number" step="0.01" id="pago_efectivo" name="pago_efectivo"
                                        class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Yape/Plin/POS</label>
                                    <select name="billetera_id" id="billetera_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($billeteras_digitales as $billetera)
                                            <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pago digital</label>
                                    <input type="number" step="0.01" id="pago_billetera" name="pago_billetera"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <button type="button" class="btn btn-warning w-100 mb-2" id="btnReservar">
                                    Reservar
                                </button>

                                <button type="button" class="btn btn-primary w-100" id="btnTerminarVenta">
                                    Terminar venta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.VENTA_CONFIG = {
            salidaId: @json($salida->id),
            origenId: @json($origen->id),
            destinoId: @json($destino->id),
            asientos: @json($asientos),
            precioUnitario: @json((float) $precioUnitario),
            descuentoPromoId: 1
        };
    </script>
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush
