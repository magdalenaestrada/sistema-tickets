@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row mt-3">
            <form method="POST" enctype="multipart/form-data" id="formVenta">
                @csrf

                <div class="row">
                    <div class="col-md-9 mb-3">
                        @foreach ($asientos as $index => $asiento)
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    <strong>Asiento {{ $asiento }}</strong>
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
                                                    <option value="{{ $tipo_documento->id }}">
                                                        {{ $tipo_documento->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Documento</label>
                                            <input type="text" class="form-control" id="documento_{{ $index }}"
                                                name="documento[]" required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Nombres</label>
                                            <input type="text" class="form-control" id="nombres_{{ $index }}"
                                                name="nombres[]" required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Apellidos</label>
                                            <input type="text" class="form-control" id="apellidos_{{ $index }}"
                                                name="apellidos[]" required>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Celular</label>
                                            <input type="text" class="form-control" id="celular_{{ $index }}"
                                                name="celular[]" required>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono_{{ $index }}"
                                                name="telefono[]">
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Correo electrónico</label>
                                            <input type="email" class="form-control" id="correo_{{ $index }}"
                                                name="direccion[]">
                                        </div>

                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Descuento</label>
                                            <input type="text" class="form-control descuento-input"
                                                data-index="{{ $index }}" id="descuento_{{ $index }}"
                                                placeholder="Código">

                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6 form-check">
                                            <input type="checkbox" class="form-check-input"
                                                id="pasajero_menor_{{ $index }}"
                                                name="pasajero_menor[{{ $index }}]" value="1">
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
                                <p class="mb-1"><strong>Precio C/U:</strong> {{ $horario->costo_pasaje }}
                                <p class="mb-1"><strong>Origen:</strong> {{ $horario->punto_origen->nombre_comercial }}
                                </p>
                                <p class="mb-1"><strong>Destino:</strong> {{ $horario->punto_destino->nombre_comercial }}
                                </p>
                                <p class="mb-1"><strong>Vehículo:</strong> {{ $horario->tipo_vehiculo->descripcion }}</p>
                                <p class="mb-1"><strong>Fecha:</strong> {{ $horario->fecha_salida->format('d-m-Y') }}</p>
                                <p class="mb-0"><strong>Hora:</strong> {{ $horario->hora_embarque }}</p>
                            </div>
                        </div>

                        {{-- FACTURACIÓN SIN EDICIÓN --}}
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

                        {{-- MÉTODO DE PAGO --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>Método de Pago</strong>
                            </div>

                            <div class="card-body">

                                <div class="mb-3">
                                    <label class="form-label">Método</label>
                                    <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                        @foreach ($metodos_pago as $metodo_pago)
                                            <option value="{{ $metodo_pago->id }}">{{ $metodo_pago->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 grupo_costo_total" hidden>
                                    <label class="form-label">Costo total</label>
                                    <input type="number" step="0.01" id="costo_total" name="costo_total"
                                        class="form-control" readonly value="{{ $horario->costo_pasaje }}">
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

                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <button type="button" class="btn btn-warning w-100 mb-2" id="btnReservar">
                                        <i class="bi bi-bookmark"></i> Reservar
                                    </button>

                                    <button type="button" class="btn btn-primary w-100" id="btnTerminarVenta">
                                        <i class="bi bi-cash-coin"></i> Terminar venta
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
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush
