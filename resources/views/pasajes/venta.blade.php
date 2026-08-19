@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-3">
        <form method="POST" enctype="multipart/form-data" id="formVenta">
            @csrf

            <input type="hidden" name="salida_id" value="{{ $salida->id }}">
            <input type="hidden" name="origen_id" value="{{ $origen->id }}">
            <input type="hidden" name="destino_id" value="{{ $destino->id }}">

            <input type="hidden" name="tipo_doc_sunat" id="tipo_doc_sunat">
            <input type="hidden" name="metodo_pago_id" id="metodo_pago_id_hidden">
            <input type="hidden" name="pago_efectivo" id="pago_efectivo_hidden">
            <input type="hidden" name="pago_tarjeta" id="pago_tarjeta_hidden">
            <input type="hidden" name="pago_yape" id="pago_yape_hidden">
            <input type="hidden" name="pago_plin" id="pago_plin_hidden">
            <input type="hidden" name="pago_transferencia" id="pago_transferencia_hidden">
            <input type="hidden" name="costo_total" id="costo_total" value="0">

            <div class="sale-header mb-4">
                <div class="sale-header-main">
                    <div class="route-section">
                        <div class="location-block">
                            <span class="location-icon">
                                <i class="link-icon" data-lucide="map-pin-house"></i>
                            </span>
                            <div>
                                <small>ORIGEN</small>
                                <strong>{{ $origen->descripcion }}</strong>
                            </div>
                        </div>

                        <div class="route-line">
                            <span></span>
                            <i class="link-icon" data-lucide="bus-front"></i>
                            <span></span>
                        </div>

                        <div class="location-block">
                            <span class="location-icon">
                                <i class="link-icon" data-lucide="map-pinned"></i>
                            </span>
                            <div>
                                <small>DESTINO</small>
                                <strong>{{ $destino->descripcion }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="trip-details">
                        <div class="detail-item">
                            <i class="bi bi-calendar3"></i>
                            <div>
                                <small>FECHA Y HORA</small>
                                <strong>
                                    {{ $salida->fecha_salida->format('d/m/Y') }}
                                    · {{ $salida->horario->hora_formateada }}
                                </strong>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="bi bi-ticket-perforated-fill"></i>
                            <div>
                                <small>ASIENTO(S)</small>
                                <div class="seat-list">
                                    @foreach ($asientos as $asiento)
                                        <span>{{ $asiento }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="price-box">
                            <small>COSTO POR ASIENTO <span style="color:red">*</span> </small>
                            <label for="precio_manual">
                                <span>S/</span>
                                <input type="number" step="0.01" min="0.01" id="precio_manual" name="precio_manual"
                                    value="" placeholder="0.00" autocomplete="off">
                            </label>
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
                                        <select class="form-select input-pasajero" name="tipo_documento_id[]"
                                            id="tipo_documento_id_{{ $index }}" required disabled>
                                            @foreach ($tipos_documentos as $tipo_documento)
                                                <option value="{{ $tipo_documento->id }}">
                                                    {{ $tipo_documento->codigo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Documento <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control solo-numeros documento-input input-pasajero"
                                                id="documento_{{ $index }}" data-index="{{ $index }}"
                                                name="documento[]" required disabled>

                                            <button type="button"
                                                class="btn btn-primary btn-buscar-documento input-pasajero"
                                                data-index="{{ $index }}" disabled>
                                                <i class="link-icon" data-lucide="search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control solo-letras input-pasajero"
                                            id="nombres_{{ $index }}" name="nombres[]" required disabled>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control solo-letras input-pasajero"
                                            id="apellidos_{{ $index }}" name="apellidos[]" required disabled>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Celular</label>
                                        <input type="text" class="form-control solo-numeros input-pasajero"
                                            id="celular_{{ $index }}" name="celular[]" maxlength="9" disabled>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control solo-numeros input-pasajero"
                                            id="telefono_{{ $index }}" name="telefono[]" maxlength="9" disabled>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo</label>
                                        <input type="email" class="form-control input-pasajero"
                                            id="correo_{{ $index }}" name="correo[]" disabled>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Descuento</label>
                                        <select class="form-select descuento-input input-pasajero"
                                            data-index="{{ $index }}" id="descuento_{{ $index }}"
                                            name="descuento_codigo[]" disabled>
                                            <option value="">Sin cupón</option>
                                        </select>
                                        <small class="text-muted d-block mt-1"
                                            id="descuento_msg_{{ $index }}"></small>
                                    </div>
                                </div>

                                <div class="row mt-3 align-items-center">
                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="observacion_{{ $index }}">Observación</label>
                                        <textarea class="form-control input-pasajero" id="observacion_{{ $index }}" name="observacion_pasaje[]"
                                            disabled></textarea>
                                    </div>
                                </div>

                                <div class="row mt-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                class="form-check-input pasajero-menor-check input-pasajero"
                                                id="pasajero_menor_{{ $index }}" data-index="{{ $index }}"
                                                name="pasajero_menor[{{ $index }}]" value="1" disabled>
                                            <label class="form-check-label" for="pasajero_menor_{{ $index }}">
                                                ¿Pasajero menor de edad?
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 autorizacion-container"
                                        id="autorizacion_container_{{ $index }}" style="display:none;">
                                        <label class="form-label">Autorización PDF </label>
                                        <input type="file" accept=".pdf,image/*" class="form-control input-pasajero"
                                            id="autorizacion_pdf_{{ $index }}" name="autorizacion_pdf[]" disabled>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input toggle-sobre-equipaje input-pasajero" type="checkbox"
                                        id="toggle_sobre_equipaje_{{ $index }}" data-index="{{ $index }}"
                                        name="registrar_sobre_equipaje[{{ $index }}]" value="1" disabled>
                                    <label class="form-check-label fw-semibold"
                                        for="toggle_sobre_equipaje_{{ $index }}">
                                        Registrar sobre equipaje para este pasajero
                                    </label>
                                </div>
                                <div id="card_sobre_equipaje_{{ $index }}" class="mt-3" style="display: none;">

                                    <div class="card border">
                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <strong>
                                                <i class="bi bi-box-seam me-1"></i>
                                                Sobre equipaje
                                            </strong>

                                            <button type="button" class="btn btn-primary btn-sm btn-agregar-sobre"
                                                data-index="{{ $index }}">
                                                <i class="bi bi-plus-lg"></i>
                                                Agregar detalle
                                            </button>
                                        </div>

                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-sm align-middle mb-2 tabla-sobre-equipaje"
                                                    id="tablaSobreEquipaje_{{ $index }}"
                                                    data-index="{{ $index }}">

                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Tipo</th>
                                                            <th>Descripción</th>
                                                            <th style="width: 120px;">Peso</th>
                                                            <th style="width: 130px;">Costo</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="text-end">
                                                <strong>
                                                    Total sobre equipaje:
                                                    S/ <span id="total_sobre_equipaje_{{ $index }}">0.00</span>
                                                </strong>
                                            </div>
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
                                        <input class="form-check-input" type="checkbox" id="emitir_sunat">
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_boleta" data-doc="boleta">
                                        Boleta
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_factura" data-doc="factura">
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
                                        <option value="{{ $caja->id }}" data-sucursal="{{ $caja->sucursal_id }}"
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
                                <label class="form-label">Documento cliente: </label>

                                <div class="input-group">
                                    <input type="text" id="doc_cliente" name="numero_documento_id"
                                        class="form-control solo-numeros">

                                    <button type="button" id="btnBuscarCliente" class="btn btn-primary">
                                        <i class="link-icon" data-lucide="search"></i>
                                    </button>
                                </div>
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
        .sale-header {
            overflow: hidden;
            color: #173d78;
            border-radius: 18px;
            background: #fff2;
            border: 2px solid #067fef5a;
            box-shadow: 0 1px 15px rgba(18, 59, 120, .18);
        }


        .sale-header-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px;
        }

        .route-section {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 360px;
        }

        .location-block,
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .location-icon {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            color: #ffffff;
            background: #6ec419;
            border-radius: 50%;
            font-size: 20px;
        }

        .location-block small,
        .detail-item small,
        .price-box small {
            display: block;
            margin-bottom: 4px;
            color: rgba(0, 0, 0, 0.68);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .location-block strong,
        .detail-item strong {
            display: block;
            font-size: 15px;
        }

        .route-line {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #173d78;
        }

        .route-line span {
            width: 28px;
            border-top: 2px dashed rgba(255, 255, 255, .55);
        }

        .trip-details {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .detail-item>i {
            color: #6ec419;
            font-size: 23px;
        }

        .seat-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .seat-list span {
            padding: 4px 9px;
            color: #ffffff;
            background: #6ec419;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
        }

        .price-box {
            min-width: 145px;
            padding: 11px 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 12px;
        }

        .price-box label {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #000000;
            font-size: 18px;
            font-weight: 800;
        }

        .price-box input {
            width: 92px;
            padding: 2px 0;
            color: #173d78;
            background: transparent;
            border: 0;
            border-bottom: 1px solid rgba(255, 255, 255, .6);
            outline: none;
            font-size: 18px;
            font-weight: 800;
            text-align: right;
        }

        .sale-header-footer {
            display: flex;
            justify-content: space-between;
            padding: 11px 24px;
            color: rgba(255, 255, 255, .75);
            background: rgba(0, 0, 0, .14);
            font-size: 12px;
        }

        .status-badge {
            color: #b9f6ce;
            font-weight: 700;
        }

        @media (max-width: 991px) {

            .sale-header-main,
            .trip-details {
                align-items: stretch;
                flex-direction: column;
            }

            .route-section {
                justify-content: center;
                min-width: auto;
            }

            .trip-details {
                gap: 14px;
            }

            .price-box {
                width: 100%;
            }
        }

        @media (max-width: 575px) {
            .route-section {
                align-items: stretch;
                flex-direction: column;
            }

            .route-line {
                justify-content: center;
                transform: rotate(90deg);
            }

            .sale-header-footer {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }
        }

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
            tiposEncomienda: @json($tiposEncomienda ?? []),
            seriesSucursal: @json($seriesSucursal)

        };
        console.log("SERIES:", window.VENTA_CONFIG.seriesSucursal);
    </script>
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush
