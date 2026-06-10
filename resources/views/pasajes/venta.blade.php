@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-3">
        <form method="POST" enctype="multipart/form-data" id="formVenta">
            @csrf

            <input type="hidden" name="salida_id" value="{{ $salida->id }}">
            <input type="hidden" name="origen_id" value="{{ $origen->id }}">
            <input type="hidden" name="destino_id" value="{{ $destino->id }}">

            <input type="hidden" name="tipo_doc_sunat" id="tipo_doc_sunat" value="4">
            <input type="hidden" name="metodo_pago_id" id="metodo_pago_id_hidden">
            <input type="hidden" name="pago_efectivo" id="pago_efectivo_hidden">
            <input type="hidden" name="pago_tarjeta" id="pago_tarjeta_hidden">
            <input type="hidden" name="pago_yape" id="pago_yape_hidden">
            <input type="hidden" name="pago_plin" id="pago_plin_hidden">
            <input type="hidden" name="pago_transferencia" id="pago_transferencia_hidden">
            <input type="hidden" name="costo_total" id="costo_total" value="0">

            <div class="card shadow-sm border-0 mb-3 resumen-top-card">
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap align-items-stretch resumen-top">
                        <div class="resumen-item">
                            <div class="resumen-label">Salida:</div>
                            <div class="resumen-value">{{ $origen->descripcion }}</div>
                        </div>

                        <div class="resumen-item">
                            <div class="resumen-label">Llegada:</div>
                            <div class="resumen-value">{{ $destino->descripcion }}</div>
                        </div>

                        <div class="resumen-item">
                            <div class="resumen-label">Fecha y hora:</div>
                            <div class="resumen-value">
                                {{ $salida->fecha_salida->format('d-m-Y') }} {{ $salida->horario->hora_formateada }}
                            </div>
                        </div>

                        <div class="resumen-item">
                            <div class="resumen-label">Numero de asiento:</div>
                            <div class="resumen-value">{{ implode(' ', $asientos) }}</div>
                        </div>

                        <div class="resumen-item resumen-item-precio">
                            <div class="resumen-label">Costo por asiento:</div>
                            <div class="resumen-value">
                                <input type="number" step="0.01" id="precio_manual" class="form-control form-control-sm"
                                    value="{{ number_format($precioUnitario, 2, '.', '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-9">
                    @foreach ($asientos as $index => $asiento)
                        <div class="card shadow-sm border-0 mb-3 asiento-card">
                            <div class="card-header bg-white border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <strong>Asiento número: {{ $asiento }}</strong>
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            <strong>Costo asiento:</strong>
                                            <span id="precio_asiento_{{ $index }}">S/
                                                {{ number_format($precioUnitario, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-3">
                                <input type="hidden" name="asientos[]" value="{{ $asiento }}">

                                <div class="row g-2">
                                    <div class="col-md-2">
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

                                    <div class="col-md-2">
                                        <label class="form-label">Documento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control solo-numeros documento-input"
                                            id="documento_{{ $index }}" data-index="{{ $index }}"
                                            name="documento[]" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control solo-letras"
                                            id="nombres_{{ $index }}" name="nombres[]" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control solo-letras"
                                            id="apellidos_{{ $index }}" name="apellidos[]" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Celular</label>
                                        <input type="text" class="form-control solo-numeros"
                                            id="celular_{{ $index }}" name="celular[]" maxlength="9">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control solo-numeros"
                                            id="telefono_{{ $index }}" name="telefono[]" maxlength="9">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo</label>
                                        <input type="email" class="form-control" id="correo_{{ $index }}"
                                            name="correo[]">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Descuento</label>
                                        <select class="form-select descuento-input" data-index="{{ $index }}"
                                            id="descuento_{{ $index }}" name="descuento_codigo[]">
                                            <option value="">Sin cupón</option>
                                        </select>
                                        <small class="text-muted d-block mt-1"
                                            id="descuento_msg_{{ $index }}"></small>
                                    </div>
                                </div>
                                <div class="row mt-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input pasajero-menor-check"
                                                id="pasajero_menor_{{ $index }}" data-index="{{ $index }}"
                                                name="pasajero_menor[{{ $index }}]" value="1">
                                            <label class="form-check-label" for="pasajero_menor_{{ $index }}">
                                                ¿Pasajero menor de edad?
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 autorizacion-container"
                                        id="autorizacion_container_{{ $index }}" style="display:none;">
                                        <label class="form-label">Autorización PDF <span
                                                class="text-danger">*</span></label>
                                        <input type="file" accept=".pdf" class="form-control"
                                            id="autorizacion_pdf_{{ $index }}" name="autorizacion_pdf[]">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input toggle-sobre-equipaje" type="checkbox"
                                        id="toggle_sobre_equipaje_{{ $index }}" data-index="{{ $index }}"
                                        name="registrar_sobre_equipaje[{{ $index }}]" value="1">

                                    <label class="form-check-label fw-semibold"
                                        for="toggle_sobre_equipaje_{{ $index }}">
                                        Registrar sobre equipaje para este pasajero
                                    </label>
                                </div>

                                <div class="card border-warning sobre-equipaje-card"
                                    id="card_sobre_equipaje_{{ $index }}" data-index="{{ $index }}"
                                    style="display:none;">

                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold mb-0">Sobre equipaje</h6>

                                            <button type="button" class="btn btn-sm btn-success btn-agregar-sobre"
                                                data-index="{{ $index }}">
                                                Agregar maleta
                                            </button>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            Pasajero:
                                            <strong id="sobre_pasajero_nombre_{{ $index }}">—</strong>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered tabla-sobre-equipaje"
                                                id="tablaSobreEquipaje_{{ $index }}"
                                                data-index="{{ $index }}">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo</th>
                                                        <th>Descripción</th>
                                                        <th>Peso KG</th>
                                                        <th>Costo S/</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>

                                        <div class="text-end fw-bold">
                                            Total sobre equipaje: S/
                                            <span id="total_sobre_equipaje_{{ $index }}">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 panel-venta">
                        <div class="card-body">
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">EMITIR SUNAT:</span>

                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="emitir_sunat" disabled>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_boleta" data-doc="boleta" disabled>
                                        Boleta
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_factura" data-doc="factura" disabled>
                                        Factura
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success doc-btn active"
                                        id="btn_nota_venta" data-doc="4">
                                        N. Venta
                                    </button>
                                </div>
                            </div>

                            <div class="mb-2 text-center fw-semibold">Sucursal de venta: <span style="color: red">*</span>
                            </div>

                            <div class="mb-3">
                                <select name="caja_id" id="caja_id" class="form-select">
                                    <option value="">Seleccionar sucursal</option>
                                    @foreach ($cajas_emision as $caja)
                                        <option value="{{ $caja->id }}"
                                            data-serie="{{ $caja->sucursal->serie->codigo ?? '001' }}"
                                            @if ($caja->sucursal_id == $user->sucursal_id) selected @endif>
                                            {{ $caja->sucursal->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2 fw-semibold">Serie sucursal:</div>
                            <div class="panel-box mb-3 text-center" id="serie_doc">Seleccionar sucursal</div>

                            <div class="resumen-totales">
                                <div class="d-flex justify-content-between">
                                    <span>Sub total:</span>
                                    <strong>S/ <span id="subtotal">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Descuentos:</span>
                                    <strong>S/ <span id="total_descuento">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between text-primary">
                                    <span>Total a pagar:</span>
                                    <strong>S/ <span id="total_pagar">0.00</span></strong>
                                </div>
                            </div>


                            <input type="hidden" name="emitir_sunat_estado" id="emitir_sunat_estado" value="0">

                            <div class="mb-2">
                                <label class="form-label">Documento cliente:</label>
                                <input type="text" id="doc_cliente" name="numero_documento_id"
                                    class="form-control solo-numeros">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Razón social:</label>
                                <input type="text" id="razon_social" name="razon_social" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control"
                                    value="-" readonly>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnRegresarAsientos">
                                    Regresar a escoger asientos
                                </button>
                                <button type="button" class="btn btn-warning w-100 mb-2" id="btnReservar">
                                    Reservar
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="btnAbrirPago">
                                    Terminar Venta
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="btnCancelarVenta">
                                    Cancelar Venta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @include('pasajes.modals.metodos_pago')
@endsection

@push('styles')
    <style>
        .resumen-top-card,
        .asiento-card,
        .panel-venta,
        #modalPago .modal-content {
            border-radius: 14px;
        }

        .resumen-top {
            width: 100%;
        }

        .resumen-item {
            flex: 1 1 180px;
            padding: 10px 16px;
            border-right: 1px solid #e5e7eb;
        }

        .resumen-item:last-child {
            border-right: none;
        }

        .resumen-item-precio {
            max-width: 180px;
        }

        .resumen-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .resumen-value {
            font-size: 14px;
            color: #111827;
        }

        .panel-box {
            border: 1px solid #cfd4dc;
            border-radius: 9px;
            min-height: 38px;
            padding: 8px 10px;
            background: #fff;
        }

        .resumen-totales {
            font-size: 14px;
            line-height: 1.9;
        }

        .asiento-card .card-header {
            font-size: 13px;
        }

        .asiento-card .form-label,
        .panel-venta .form-label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .asiento-card .form-control,
        .asiento-card .form-select,
        .panel-venta .form-control {
            font-size: 13px;
            border-radius: 8px;
        }

        .modal-total-title {
            color: #7f87ff;
            font-weight: 800;
        }

        .modal-total-amount {
            font-size: 40px;
            font-weight: 800;
        }

        .metodo-label {
            background: #eef2f7;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
        }

        .doc-btn.active {
            color: #fff !important;
        }

        @media (max-width: 991px) {
            .resumen-item {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .resumen-item:last-child {
                border-bottom: none;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.VENTA_CONFIG = {
            salidaId: @json($salida->id),
            origenId: @json($origen->id),
            destinoId: @json($destino->id),
            asientos: @json($asientos),
            precioUnitario: @json((float) $precioUnitario),
            descuentoPromoId: 1,
            volverAsientosUrl: @json(url()->previous()),
            tiposEncomienda: @json($tiposEncomienda ?? [])

        };
    </script>
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush
