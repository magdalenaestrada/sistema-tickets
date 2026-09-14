<div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content rounded-4 border-0 shadow-lg">

            {{-- ====================================================== --}}
            {{-- HEADER --}}
            {{-- ====================================================== --}}
            <div class="modal-header border-bottom-0 pb-0">

                <div class="d-flex align-items-center gap-2">

                    <button type="button" id="btnVolverModal" class="btn btn-sm btn-light border rounded-circle d-none"
                        onclick="volverPasoComprobante()" title="Volver">

                        <i class="bi bi-chevron-left"></i>

                    </button>

                    <div>

                        <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="modalComprobanteLabel">
                            Generar comprobante
                        </h5>

                        <small class="text-muted" id="subtituloModalComprobante">

                            Selecciona cómo deseas generar el comprobante.

                        </small>

                    </div>

                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar">
                </button>

            </div>


            <div class="modal-body p-4">

                {{-- ====================================================== --}}
                {{-- STEPPER GENERAL --}}
                {{-- ====================================================== --}}

                <div class="d-flex justify-content-between align-items-center mb-4 px-md-4">

                    {{-- PASO 1 --}}
                    <div class="d-flex align-items-center gap-2">

                        <span id="stepBadge1"
                            class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                            style="width:30px;height:30px;">
                            1
                        </span>

                        <span id="stepText1" class="fw-semibold text-primary small">
                            Inicio
                        </span>

                    </div>

                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>


                    {{-- PASO 2 --}}
                    <div class="d-flex align-items-center gap-2">

                        <span id="stepBadge2"
                            class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                            style="width:30px;height:30px;">
                            2
                        </span>

                        <span id="stepText2" class="text-muted small">
                            Documento
                        </span>

                    </div>

                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>


                    {{-- PASO 3 --}}
                    <div class="d-flex align-items-center gap-2">

                        <span id="stepBadge3"
                            class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                            style="width:30px;height:30px;">
                            3
                        </span>

                        <span id="stepText3" class="text-muted small">
                            Información
                        </span>

                    </div>

                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>


                    {{-- PASO 4 --}}
                    <div class="d-flex align-items-center gap-2">

                        <span id="stepBadge4"
                            class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                            style="width:30px;height:30px;">
                            4
                        </span>

                        <span id="stepText4" class="text-muted small">
                            Revisar
                        </span>

                    </div>

                </div>

                <hr class="text-muted opacity-25 mb-4">


                {{-- ====================================================== --}}
                {{-- PASO 1 --}}
                {{-- NUEVO O DESDE EXISTENTE --}}
                {{-- ====================================================== --}}

                <div id="paso1Comprobante">

                    <div class="text-center mb-4">

                        <h5 class="fw-bold text-dark mb-1">
                            ¿Cómo deseas generar el comprobante?
                        </h5>

                        <p class="text-muted small mb-0">
                            Selecciona una opción para continuar.
                        </p>

                    </div>


                    <div class="row g-4 justify-content-center">

                        {{-- NUEVO --}}
                        <div class="col-md-5">

                            <div class="card h-100 border shadow-sm rounded-4 p-4">

                                <div class="text-center">

                                    <div class="d-inline-flex align-items-center justify-content-center
                                                bg-success bg-opacity-10 text-success rounded-circle mb-3"
                                        style="width:60px;height:60px;">

                                        <i class="bi bi-file-earmark-plus fs-3"></i>

                                    </div>

                                    <h5 class="fw-bold">
                                        Nuevo comprobante
                                    </h5>

                                    <p class="text-muted small">
                                        Crear una boleta, factura u otro documento
                                        desde cero.
                                    </p>

                                    <button type="button" class="btn btn-outline-success w-100 mt-3"
                                        onclick="iniciarComprobanteNuevo()">

                                        Generar nuevo comprobante

                                        <i class="bi bi-arrow-right ms-1"></i>

                                    </button>

                                </div>

                            </div>

                        </div>


                        {{-- EXISTENTE --}}
                        <div class="col-md-5">

                            <div class="card h-100 border shadow-sm rounded-4 p-4">

                                <div class="text-center">

                                    <div class="d-inline-flex align-items-center justify-content-center
                                                bg-primary bg-opacity-10 text-primary rounded-circle mb-3"
                                        style="width:60px;height:60px;">

                                        <i class="bi bi-files fs-3"></i>

                                    </div>

                                    <h5 class="fw-bold">
                                        A partir de un existente
                                    </h5>

                                    <p class="text-muted small">
                                        Buscar una nota de venta, boleta o factura
                                        y generar otro documento.
                                    </p>

                                    <button type="button" class="btn btn-outline-primary w-100 mt-3"
                                        onclick="iniciarDesdeExistente()">

                                        Buscar comprobante

                                        <i class="bi bi-arrow-right ms-1"></i>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ====================================================== --}}
                {{-- PASO 2 --}}
                {{-- BUSCAR COMPROBANTE Y ELEGIR QUÉ GENERAR --}}
                {{-- ====================================================== --}}

                <div id="paso2Comprobante" class="d-none">

                    <div class="mb-4">

                        <h6 class="fw-bold text-dark mb-1">
                            Buscar comprobante de origen
                        </h6>

                        <p class="text-muted small">
                            Puedes buscar por serie, número, DNI, RUC o cliente.
                        </p>

                    </div>


                    <div class="input-group mb-3">

                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>

                        <input type="text" id="buscar_comprobante_input" class="form-control"
                            placeholder="Ej. BV01-125, DNI, RUC o cliente">

                        <button type="button" class="btn btn-primary" onclick="buscarComprobanteReferencia()">

                            Buscar

                        </button>

                    </div>


                    <div id="resultado_busqueda_comprobante" class="mb-3">
                    </div>


                    <input type="hidden" id="referencia_venta_id">

                    <select id="tipo_comprobante_destino" class="d-none">
                        <option value="">Seleccionar</option>

                        @foreach ($tiposDocumento as $tipo)
                            <option value="{{ $tipo->id }}">
                                {{ $tipo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    {{-- DOCUMENTO SELECCIONADO --}}
                    <div class="card border-0 bg-light rounded-3 p-3 mb-3">

                        <div class="row align-items-center">

                            <div class="col-md-8">

                                <small class="text-muted d-block">
                                    Documento seleccionado
                                </small>

                                <strong id="texto_documento_referencia">
                                    Ninguno seleccionado
                                </strong>

                            </div>

                            <div class="col-md-4 text-md-end mt-2 mt-md-0">

                                <small class="text-muted d-block">
                                    Total
                                </small>

                                <strong id="total_a_emitir" class="text-primary fs-5">
                                    S/ 0.00
                                </strong>

                            </div>

                        </div>

                    </div>


                    {{-- OPCIONES SEGÚN DOCUMENTO --}}
                    <div id="opcionesConversion" class="d-none mt-4">

                        <h6 class="fw-bold mb-3">
                            ¿Qué deseas generar?
                        </h6>

                        <div class="row g-3" id="contenedorOpcionesConversion">
                        </div>

                    </div>


                    <div id="aviso_anulacion_origen" class="alert alert-info mt-3" style="display:none;">
                    </div>

                </div>


                {{-- ====================================================== --}}
                {{-- PASO 3 --}}
                {{-- COMPLETAR INFORMACIÓN --}}
                {{-- ====================================================== --}}

                <div id="paso3Comprobante" class="d-none">


                    {{-- ================================================== --}}
                    {{-- NUEVO COMPROBANTE --}}
                    {{-- ================================================== --}}

                    <div id="formularioComprobanteNuevo" class="d-none">

                        <form method="POST" action="{{ route('facturacion.pos.store') }}" id="formVentaRapida">

                            @csrf


                            {{-- DATOS EMISIÓN --}}
                            <div class="card bg-light border-0 rounded-3 p-3 mb-3">

                                <h6 class="fw-bold mb-3">
                                    Datos de emisión
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-3">

                                        <label class="form-label small fw-semibold">
                                            Sucursal
                                        </label>

                                        <select id="caja_id" name="caja_id" class="form-select form-select-sm"
                                            required>

                                            @foreach ($cajas as $caja)
                                                <option value="{{ $caja->id }}"
                                                    data-series='@json($caja->sucursal->serie->pluck('serie', 'tipo_documento_factura_id'))'>

                                                    {{ $caja->sucursal->nombre_comercial }}

                                                </option>
                                            @endforeach

                                        </select>

                                    </div>


                                    <div class="col-md-3">

                                        <label class="form-label small fw-semibold">
                                            Tipo documento
                                        </label>

                                        <select id="tipo_documento_modal" name="tipo_documento_factura_id"
                                            class="form-select form-select-sm" required>

                                            <option value="">
                                                Seleccionar
                                            </option>

                                            @foreach ($tiposDocumento as $tipo)
                                                <option value="{{ $tipo->id }}">
                                                    {{ $tipo->descripcion }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>


                                    <div class="col-md-3">

                                        <label class="form-label small fw-semibold">
                                            Serie
                                        </label>

                                        <input type="text" id="serie" class="form-control form-control-sm"
                                            readonly>

                                    </div>


                                    <div class="col-md-3">

                                        <label class="form-label small fw-semibold">
                                            Fecha emisión
                                        </label>

                                        <input type="date" class="form-control form-control-sm"
                                            value="{{ now()->format('Y-m-d') }}" readonly>

                                    </div>

                                </div>

                            </div>


                            {{-- CLIENTE --}}
                            <div class="card border shadow-sm rounded-3 p-3 mb-3">

                                <h6 class="fw-bold mb-3">
                                    Datos del cliente
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-4">

                                        <label class="form-label small">
                                            Documento
                                        </label>

                                        <div class="input-group input-group-sm">

                                            <input type="text" id="doc_cliente" name="documento"
                                                class="form-control" placeholder="DNI / RUC" required>

                                            <button type="button" id="btnBuscarCliente" class="btn btn-primary"
                                                onclick="buscarCliente()">

                                                Buscar

                                            </button>

                                        </div>

                                    </div>


                                    <div class="col-md-4">

                                        <label id="lblNombre" class="form-label small">
                                            Nombres
                                        </label>

                                        <input type="text" id="nombres" name="nombres"
                                            class="form-control form-control-sm" required>

                                    </div>


                                    <div class="col-md-4" id="divApellidos">

                                        <label class="form-label small">
                                            Apellidos
                                        </label>

                                        <input type="text" id="apellidos" name="apellidos"
                                            class="form-control form-control-sm">

                                    </div>


                                    <div class="col-12">

                                        <label class="form-label small">
                                            Dirección
                                        </label>

                                        <input type="text" id="direccion" name="direccion"
                                            class="form-control form-control-sm">

                                    </div>

                                </div>

                            </div>


                            {{-- DETALLES --}}
                            <div class="card border shadow-sm rounded-3 p-3">

                                <h6 class="fw-bold mb-3">
                                    Detalle del servicio
                                </h6>

                                <div class="row g-2 mb-3">

                                    <div class="col-md-2">

                                        <select id="tipo_servicio_id" name="tipo_servicio_id"
                                            class="form-select form-select-sm">

                                            <option value="1">
                                                Pasaje
                                            </option>

                                            <option value="2">
                                                Encomienda
                                            </option>

                                            <option value="3">
                                                Sobreequipaje
                                            </option>

                                        </select>

                                    </div>


                                    <div class="col-md-4">

                                        <input type="text" id="descripcion" class="form-control form-control-sm"
                                            placeholder="Descripción">

                                    </div>


                                    <div class="col-md-2">

                                        <input type="number" step="0.01" id="unidad"
                                            class="form-control form-control-sm" placeholder="Cantidad">

                                    </div>


                                    <div class="col-md-2">

                                        <input type="number" step="0.01" id="precio"
                                            class="form-control form-control-sm" placeholder="Precio">

                                    </div>


                                    <div class="col-md-2">

                                        <button type="button" class="btn btn-success btn-sm w-100"
                                            onclick="agregarItem()">

                                            <i class="bi bi-plus-lg"></i>
                                            Agregar

                                        </button>

                                    </div>

                                </div>


                                <div class="table-responsive">

                                    <table class="table table-sm table-bordered align-middle">

                                        <thead class="table-light">

                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio</th>
                                                <th>Valor s/IGV</th>
                                                <th>IGV</th>
                                                <th>Total</th>
                                                <th></th>
                                            </tr>

                                        </thead>

                                        <tbody id="tablaItems"></tbody>

                                    </table>

                                </div>


                                <input type="hidden" name="items" id="itemsInput">


                                <div class="d-flex justify-content-end mt-3">

                                    <div class="bg-light p-3 rounded-3" style="min-width:250px;">

                                        <div class="d-flex justify-content-between">
                                            <span>Subtotal</span>
                                            <strong>
                                                S/ <span id="subtotal">0.00</span>
                                            </strong>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <span>IGV</span>
                                            <strong>
                                                S/ <span id="igv">0.00</span>
                                            </strong>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between fs-5">
                                            <strong>Total</strong>
                                            <strong>
                                                S/ <span id="total">0.00</span>
                                            </strong>
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="d-flex justify-content-end mt-4">

                                <button type="button" class="btn btn-primary px-4"
                                    onclick="continuarNuevoComprobante()">

                                    Continuar

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </button>

                            </div>

                        </form>

                    </div>



                    {{-- ================================================== --}}
                    {{-- DESDE EXISTENTE --}}
                    {{-- ================================================== --}}

                    <div id="formularioConversionExistente" class="d-none">


                        {{-- FECHA --}}
                        <div class="card bg-light border-0 rounded-3 p-3 mb-3">

                            <div class="row">

                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        Fecha de emisión
                                    </label>

                                    <input type="date" id="fecha_emision_destino"
                                        value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm"
                                        readonly>

                                </div>

                            </div>

                        </div>


                        {{-- CLIENTE --}}
                        <div class="campo-cliente card border shadow-sm rounded-3 p-3 mb-3">

                            <h6 class="fw-bold mb-3">
                                Datos del cliente
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">

                                        <span id="lblDocumentoConversion">
                                            DNI / RUC
                                        </span>

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group input-group-sm">

                                        <input type="text" id="doc_cliente_conversion" class="form-control"
                                            maxlength="11" autocomplete="off">

                                        <button type="button" id="btnBuscarClienteConversion"
                                            class="btn btn-primary" onclick="buscarClienteConversion()">

                                            <i class="bi bi-search"></i>
                                            Buscar

                                        </button>

                                    </div>

                                    <small id="ayudaDocumentoConversion" class="text-muted">
                                    </small>

                                </div>


                                <div class="col-md-4">

                                    <label id="lblNombreConversion" class="form-label small fw-semibold">
                                        Cliente
                                    </label>

                                    <input type="text" id="nombre_cliente_conversion"
                                        class="form-control form-control-sm" readonly>

                                </div>


                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold">
                                        Dirección
                                    </label>

                                    <input type="text" id="direccion_cliente_conversion"
                                        class="form-control form-control-sm" readonly>

                                </div>

                            </div>

                        </div>


                        {{-- CAMPOS FACTURA --}}
                        <div class="campo-factura d-none"></div>

                        {{-- CAMPOS BOLETA --}}
                        <div class="campo-boleta d-none"></div>


                        {{-- NOTA DE CRÉDITO --}}
                        <div class="campo-nota-credito d-none">

                            <div class="card border-warning shadow-sm rounded-3 p-3 mb-3">

                                <h6 class="fw-bold mb-3">
                                    Nota de crédito
                                </h6>

                                <div class="row g-3 mb-3">

                                    <div class="col-md-4">

                                        <small class="text-muted d-block">
                                            Documento origen
                                        </small>

                                        <strong id="nc_documento_origen">
                                            -
                                        </strong>

                                    </div>


                                    <div class="col-md-4">

                                        <small class="text-muted d-block">
                                            Cliente
                                        </small>

                                        <strong id="nc_cliente">
                                            -
                                        </strong>

                                    </div>


                                    <div class="col-md-4">

                                        <small class="text-muted d-block">
                                            Total
                                        </small>

                                        <strong id="nc_total">
                                            S/ 0.00
                                        </strong>

                                    </div>

                                </div>


                                <label class="form-label small fw-semibold">
                                    Motivo de la nota de crédito
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea id="motivo_nota_credito" class="form-control" rows="3" placeholder="Ingrese el motivo"></textarea>

                            </div>

                        </div>


                        <div class="d-flex justify-content-end mt-4">

                            <button type="button" class="btn btn-primary px-4" onclick="continuarConversion()">

                                Continuar

                                <i class="bi bi-arrow-right ms-1"></i>

                            </button>

                        </div>

                    </div>

                </div>



                {{-- ====================================================== --}}
                {{-- PASO 4 --}}
                {{-- PREVISUALIZAR --}}
                {{-- ====================================================== --}}

                <div id="paso4Comprobante" class="d-none">


                    <div class="text-center mb-4">

                        <div class="d-inline-flex align-items-center justify-content-center
                                    bg-primary bg-opacity-10 text-primary rounded-circle mb-2"
                            style="width:55px;height:55px;">

                            <i class="bi bi-file-earmark-check fs-3"></i>

                        </div>

                        <h5 class="fw-bold mb-1">
                            Revisar comprobante
                        </h5>

                        <p class="text-muted small">
                            Verifica la información antes de emitir.
                        </p>

                    </div>


                    <div class="card border shadow-sm rounded-4">

                        <div class="card-body p-4">


                            <div class="row g-4">

                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Tipo de documento
                                    </small>

                                    <strong id="preview_tipo">
                                        -
                                    </strong>

                                </div>


                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Fecha de emisión
                                    </small>

                                    <strong id="preview_fecha">
                                        {{ now()->format('d/m/Y') }}
                                    </strong>

                                </div>


                                <div class="col-md-4">

                                    <small class="text-muted d-block">
                                        Documento origen
                                    </small>

                                    <strong id="preview_origen">
                                        -
                                    </strong>

                                </div>


                                <div class="col-md-6">

                                    <small class="text-muted d-block">
                                        Cliente
                                    </small>

                                    <strong id="preview_cliente">
                                        -
                                    </strong>

                                </div>


                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Documento
                                    </small>

                                    <strong id="preview_documento">
                                        -
                                    </strong>

                                </div>


                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Total
                                    </small>

                                    <strong id="preview_total" class="text-primary fs-5">
                                        S/ 0.00
                                    </strong>

                                </div>


                                <div id="preview_motivo_container" class="col-12 d-none">

                                    <div class="alert alert-warning mb-0">

                                        <small class="text-muted d-block">
                                            Motivo de nota de crédito
                                        </small>

                                        <strong id="preview_motivo">
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <button type="button" class="btn btn-light border px-4" onclick="volverPasoComprobante()">

                            <i class="bi bi-arrow-left me-1"></i>
                            Volver

                        </button>


                        <button type="button" id="btnEmitirComprobante" class="btn btn-success px-4 fw-semibold"
                            onclick="emitirComprobanteFinal()">

                            <i class="bi bi-send me-1"></i>
                            Emitir comprobante

                        </button>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>
