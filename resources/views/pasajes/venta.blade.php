@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row mt-3">
            <div class="col-md-9 mb-3">
                @foreach ($asientos as $asiento)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <strong>Asiento {{ $asiento }}</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('pasajes.guardar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="asientos" value="{{ implode(',', $asientos) }}">
                                <input type="hidden" name="horario_id" value="{{ $horario->id }}">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" name="tipo_documento_id" id="tipo_documento_id"
                                            required>
                                            @foreach ($tipos_documentos as $tipo_documento)
                                                <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Documento</label>
                                        <input type="text" class="form-control" id="documento" name="documento" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Nombres</label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Celular</label>
                                        <input type="text" class="form-control" id="celular" name="celular" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Telefono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo electrónico</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Descuento</label>
                                        <input type="text" class="form-control" id="descuento" name="descuento">
                                    </div>
                                    <div class="row py-3">
                                        <div class="col-md-4 form-check">
                                            <input type="checkbox" class="form-check-input" id="pasajero_menor">
                                            <label class="form-check-label" for="pasajero_menor">¿Pasajero menor de
                                                edad?</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="file" accept=".pdf class="form-file" id="autorizacion_pdf"
                                                name="autorizacion_pdf">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <br>
                @endforeach
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3"> <strong>ASIENTOS: {{ implode(', ', $asientos) }}</h6> </strong>
                        <p>
                            <strong>Origen:</strong> {{ $horario->punto_origen->nombre_comercial }}<br>
                            <strong>Destino:</strong> {{ $horario->punto_destino->nombre_comercial }} <br>
                            <strong>Vehículo:</strong> {{ $horario->tipo_vehiculo->descripcion }} <br>
                            <strong>Fecha:</strong> {{ $horario->fecha_salida->format('d-m-Y') }} <br>
                            <strong>Hora:</strong> {{ $horario->hora_embarque }} <br>
                        </p>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tipo_documento_factura_id" class="col-6 col-form-label">Tipo de
                                documento</label>
                            <select name="tipo_documento_factura_id" id="tipo_documento_factura_id" class="form-select">
                                @foreach ($tipos_documentos_facturas as $index => $tipo_documento_factura)
                                    <option value="{{ $tipo_documento_factura->id }}"
                                        @if ($index === 1) selected @endif>
                                        {{ $tipo_documento_factura->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="numero_documento_id" class="form-label">Número documento</label>
                            <input type="number" id="numero_documento_id" name="numero_documento_id" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="razon_social" class="form-label">Razón social</label>
                            <input type="text" id="razon_social" name="razon_social" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <div class="card mb-3">
                    <div class="card-body">

                        <div class="row mb-2">
                            <label for="metodo_pago_id" class="col-6 col-form-label">Método de pago</label>
                            <div class="col-6">
                                <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                    @foreach ($metodos_pago as $metodo_pago)
                                        <option value="{{ $metodo_pago->id }}">
                                            {{ $metodo_pago->descripcion }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="row mb-2 grupo_costo_total" hidden>
                            <label for="costo_total" class="col-6 col-form-label">Costo total</label>
                            <div class="col-6">
                                <input type="number" id="costo_total" name="costo_total" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label for="pago_efectivo" class="col-6 col-form-label">Pago efectivo</label>
                            <div class="col-6">
                                <input type="number" id="pago_efectivo" name="pago_efectivo" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="billetera_id" class="col-6 col-form-label">Yape/Plin/POS</label>
                            <div class="col-6">
                                <select name="billetera_id" id="billetera_id" class="form-select">
                                    @foreach ($billeteras_digitales as $billetera_digital)
                                        <option value="{{ $billetera_digital->id }}">
                                            {{ $billetera_digital->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="pago_billetera" class="col-6 col-form-label">Pago digital</label>
                            <div class="col-6">
                                <input type="number" id="pago_billetera" name="pago_billetera" class="form-control">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <button class="btn btn-warning w-100 mb-2" id="btnReservar">
                            Reservar
                        </button>

                        <button class="btn btn-primary w-100" id="btnTerminarVenta">
                            Terminar venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush