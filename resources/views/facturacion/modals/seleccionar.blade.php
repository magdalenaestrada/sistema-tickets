<div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">

            {{-- HEADER GENERAL --}}
            <div class="modal-header border-bottom-0 pb-0">

                <div class="d-flex align-items-center gap-2">

                    {{-- Este botón solo aparece cuando estamos dentro de una opción --}}
                    <button type="button" id="btnVolverModal" class="btn btn-sm btn-light border rounded-circle d-none"
                        onclick="volverOpciones()" title="Volver">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div>
                        <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="modalComprobanteLabel">
                            Nuevo comprobante
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

                {{-- ========================================================= --}}
                {{-- PASO 1: SELECCIONAR OPCIÓN --}}
                {{-- ========================================================= --}}

                <div id="pasoOpciones">

                    {{-- STEPPER --}}
                    <div class="d-flex justify-content-between align-items-center my-3 px-md-4">

                        <div class="d-flex align-items-center gap-2">
                            <span
                                class="badge rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">
                                1
                            </span>

                            <span class="fw-semibold text-primary small">
                                Seleccionar opción
                            </span>
                        </div>

                        <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>

                        <div class="d-flex align-items-center gap-2">
                            <span
                                class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">
                                2
                            </span>

                            <span class="text-muted small">
                                Completar información
                            </span>
                        </div>

                        <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>

                        <div class="d-flex align-items-center gap-2">
                            <span
                                class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">
                                3
                            </span>

                            <span class="text-muted small">
                                Revisar y emitir
                            </span>
                        </div>

                    </div>


                    <hr class="text-muted opacity-25 my-4">


                    <div class="text-center mb-4">

                        <h6 class="fw-bold text-dark fs-5 mb-1">
                            ¿Cómo deseas generar tu comprobante?
                        </h6>

                        <p class="text-muted small mb-0">
                            Selecciona una de las siguientes opciones para continuar.
                        </p>

                    </div>


                    <div class="row g-3">

                        {{-- GENERAR NUEVO --}}
                        <div class="col-md-6">

                            <div class="card h-100 border rounded-3 p-4 shadow-sm">

                                <div class="d-flex align-items-start gap-3 mb-2">

                                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                                        <i class="bi bi-file-earmark-plus fs-4"></i>
                                    </div>

                                    <div>

                                        <h6 class="fw-bold text-dark mb-1">
                                            Generar nuevo comprobante
                                        </h6>

                                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                            Crea un comprobante desde cero con los datos
                                            del cliente y servicios.
                                        </p>

                                    </div>

                                </div>


                                <ul class="list-unstyled my-3 text-secondary" style="font-size: 0.8rem;">

                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        Comprobante totalmente nuevo
                                    </li>

                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        Ingresa cliente y servicios
                                    </li>

                                    <li>
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        Se generará un nuevo número
                                    </li>

                                </ul>


                                <button type="button" class="btn btn-outline-success btn-sm w-100 mt-auto fw-semibold"
                                    onclick="mostrarNuevoComprobante()">

                                    Generar nuevo comprobante

                                    <i class="bi bi-chevron-right ms-1"></i>

                                </button>

                            </div>

                        </div>


                        {{-- USAR EXISTENTE --}}
                        <div class="col-md-6">

                            <div class="card h-100 border rounded-3 p-4 shadow-sm">

                                <div class="d-flex align-items-start gap-3 mb-2">

                                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                                        <i class="bi bi-file-earmark-text fs-4"></i>
                                    </div>

                                    <div>

                                        <h6 class="fw-bold text-dark mb-1">
                                            Generar a partir de existente
                                        </h6>

                                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                            Usa un comprobante ya emitido como referencia
                                            para generar otro documento.
                                        </p>

                                    </div>

                                </div>


                                <ul class="list-unstyled my-3 text-secondary" style="font-size: 0.8rem;">

                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-primary me-1"></i>
                                        Usa un comprobante como referencia
                                    </li>

                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-primary me-1"></i>
                                        Convierte a otro tipo de comprobante
                                    </li>

                                    <li>
                                        <i class="bi bi-check-circle-fill text-primary me-1"></i>
                                        Mantiene cliente y detalle
                                    </li>

                                </ul>


                                <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-auto fw-semibold"
                                    onclick="mostrarComprobanteExistente()">

                                    Usar comprobante existente

                                    <i class="bi bi-chevron-right ms-1"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- ========================================================= --}}
                {{-- PASO 2A: NUEVO COMPROBANTE --}}
                {{-- ========================================================= --}}

                <div id="pasoNuevo" class="d-none">

                    <form method="POST" action="{{ route('facturacion.pos.store') }}" id="formVentaRapida">

                        @csrf


                        {{-- STEPPER --}}
                        <div class="d-flex justify-content-between align-items-center my-3 px-md-4">

                            <div class="d-flex align-items-center gap-2">

                                <span
                                    class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px;">

                                    <i class="bi bi-check-lg"></i>

                                </span>

                                <span class="text-primary small">
                                    Seleccionar opción
                                </span>

                            </div>


                            <div class="flex-grow-1 mx-3 border-top border-2 border-primary"></div>


                            <div class="d-flex align-items-center gap-2">

                                <span
                                    class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px;">
                                    2
                                </span>

                                <span class="fw-semibold text-primary small">
                                    Completar información
                                </span>

                            </div>


                            <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>


                            <div class="d-flex align-items-center gap-2">

                                <span
                                    class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px;">
                                    3
                                </span>

                                <span class="text-muted small">
                                    Revisar y emitir
                                </span>

                            </div>

                        </div>


                        <hr class="text-muted opacity-25 my-4">


                        {{-- DATOS DE EMISIÓN --}}
                        <div class="card bg-light border-0 rounded-3 p-3 mb-3">

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold text-secondary">
                                        Sucursal
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select id="caja_id" name="caja_id" class="form-select form-select-sm"
                                        required>

                                        @foreach ($cajas as $caja)
                                            <option value="{{ $caja->id }}"
                                                data-series='@json($caja->sucursal->serie->pluck('serie', 'tipo_documento_factura_id'))'>

                                                {{ $caja->sucursal->nombre_comercial }}
                                                —
                                                {{ $caja->usuario->persona->nombre_completo }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>


                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold text-secondary">
                                        Tipo documento
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select id="tipo_documento_modal" name="tipo_documento_factura_id"
                                        class="form-select form-select-sm" required>

                                        <option value="">
                                            Seleccionar un tipo
                                        </option>

                                        @foreach ($tiposDocumento as $tipo)
                                            <option value="{{ $tipo->id }}">
                                                {{ $tipo->descripcion }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>


                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold text-secondary">
                                        Serie
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" id="serie"
                                        class="form-control form-control-sm bg-white" readonly required>

                                </div>

                            </div>

                        </div>



                        {{-- DATOS CLIENTE --}}
                        <div class="card border-0 border-start border-primary border-3 shadow-sm rounded-3 p-3 mb-3">

                            <h6 class="fw-bold text-dark mb-3 small">

                                <i class="bi bi-person me-1"></i>

                                Datos del Cliente

                            </h6>


                            <div class="row g-3">

                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold text-secondary">

                                        Documento

                                        <span class="text-danger">*</span>

                                    </label>


                                    <div class="input-group input-group-sm">

                                        <input type="text" id="doc_cliente" name="documento" class="form-control"
                                            placeholder="DNI o RUC" required>


                                        <button type="button" id="btnBuscarCliente" class="btn btn-primary"
                                            onclick="buscarCliente()">

                                            <i class="bi bi-search me-1"></i>
                                            Buscar

                                        </button>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <label id="lblNombre" class="form-label small fw-semibold text-secondary">

                                        Nombres

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input type="text" id="nombres" name="nombres"
                                        class="form-control form-control-sm" required>

                                </div>


                                <div class="col-md-4" id="divApellidos">

                                    <label class="form-label small fw-semibold text-secondary">

                                        Apellidos

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input type="text" id="apellidos" name="apellidos"
                                        class="form-control form-control-sm" required>

                                </div>


                                <div class="col-12">

                                    <label class="form-label small fw-semibold text-secondary">
                                        Dirección
                                    </label>

                                    <input type="text" id="direccion" name="direccion"
                                        class="form-control form-control-sm">

                                </div>

                            </div>

                        </div>



                        {{-- DETALLE --}}
                        <div class="card border-0 shadow-sm rounded-3 p-3">

                            <h6 class="fw-bold text-dark mb-3 small">

                                <i class="bi bi-box-seam me-1"></i>

                                Detalle del Servicio

                            </h6>


                            <div class="row g-2 mb-3">

                                <div class="col-md-2">

                                    <select name="tipo_servicio_id" id="tipo_servicio_id"
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
                                        placeholder="Escribir descripción">

                                </div>


                                <div class="col-md-2">

                                    <input type="number" step="0.01" id="unidad"
                                        class="form-control form-control-sm" placeholder="Unidades">

                                </div>


                                <div class="col-md-2">

                                    <input type="number" step="0.01" id="precio"
                                        class="form-control form-control-sm" placeholder="Precio (incl. IGV)">

                                </div>


                                <div class="col-md-2">

                                    <button type="button" class="btn btn-success btn-sm w-100 fw-bold"
                                        onclick="agregarItem()">

                                        <i class="bi bi-plus-lg"></i>
                                        Agregar

                                    </button>

                                </div>

                            </div>


                            <div class="table-responsive">

                                <table class="table table-sm table-bordered align-middle text-center mb-0">

                                    <thead class="table-light small">

                                        <tr>

                                            <th width="90">
                                                Tipo
                                            </th>

                                            <th>
                                                Descripción
                                            </th>

                                            <th width="90">
                                                Unidades
                                            </th>

                                            <th width="110">
                                                P. Unit. (c/IGV)
                                            </th>

                                            <th width="110">
                                                Valor s/IGV
                                            </th>

                                            <th width="90">
                                                IGV
                                            </th>

                                            <th width="110">
                                                Subtotal
                                            </th>

                                            <th width="60">
                                                Acciones
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody id="tablaItems" class="small"></tbody>

                                </table>

                            </div>


                            <input type="hidden" name="items" id="itemsInput">


                            {{-- TOTALES --}}
                            <div class="d-flex justify-content-end mt-3">

                                <div class="bg-light p-3 rounded-3 text-end" style="min-width: 240px;">

                                    <div class="d-flex justify-content-between text-muted small mb-1">

                                        <span>
                                            Subtotal:
                                        </span>

                                        <span>
                                            S/
                                            <span id="subtotal">
                                                0.00
                                            </span>
                                        </span>

                                    </div>


                                    <div class="d-flex justify-content-between text-muted small mb-1">

                                        <span>
                                            IGV:
                                        </span>

                                        <span>
                                            S/
                                            <span id="igv">
                                                0.00
                                            </span>
                                        </span>

                                    </div>


                                    <hr class="my-1">


                                    <div class="d-flex justify-content-between fw-bold text-dark fs-5">

                                        <span>
                                            Total:
                                        </span>

                                        <span>
                                            S/
                                            <span id="total">
                                                0.00
                                            </span>
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- ACCIONES --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">

                            <button type="button" class="btn btn-light btn-sm px-4 border"
                                onclick="volverOpciones()">

                                <i class="bi bi-chevron-left me-1"></i>
                                Volver

                            </button>


                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">

                                <i class="bi bi-check-circle me-1"></i>

                                Generar y Emitir

                            </button>

                        </div>

                    </form>

                </div>



                {{-- ========================================================= --}}
                {{-- PASO 2B: COMPROBANTE EXISTENTE --}}
                {{-- ========================================================= --}}

                <div id="pasoExistente" class="d-none">

                    {{-- STEPPER --}}
                    <div class="d-flex justify-content-between align-items-center my-3 px-md-4">

                        <div class="d-flex align-items-center gap-2">

                            <span
                                class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">

                                <i class="bi bi-check-lg"></i>

                            </span>

                            <span class="text-primary small">
                                Seleccionar opción
                            </span>

                        </div>


                        <div class="flex-grow-1 mx-3 border-top border-2 border-primary"></div>


                        <div class="d-flex align-items-center gap-2">

                            <span
                                class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">
                                2
                            </span>

                            <span class="fw-semibold text-primary small">
                                Completar información
                            </span>

                        </div>


                        <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>


                        <div class="d-flex align-items-center gap-2">

                            <span
                                class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                style="width: 28px; height: 28px;">
                                3
                            </span>

                            <span class="text-muted small">
                                Revisar y emitir
                            </span>

                        </div>

                    </div>


                    <hr class="text-muted opacity-25 my-4">


                    {{-- BUSCAR COMPROBANTE --}}
                    <div class="mb-4">

                        <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">

                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>

                            1. Comprobante de referencia

                        </h6>


                        <label class="form-label small fw-semibold text-secondary">

                            Buscar comprobante

                            <span class="text-danger">*</span>

                        </label>


                        <div class="input-group input-group-sm mb-3">

                            <span class="input-group-text bg-white border-end-0 text-muted">

                                <i class="bi bi-search"></i>

                            </span>


                            <input type="text" id="buscar_comprobante_input" class="form-control border-start-0"
                                placeholder="Ingrese serie, número o cliente...">


                            <button type="button" class="btn btn-outline-primary px-3 fw-semibold"
                                onclick="buscarComprobanteReferencia()">

                                Buscar

                            </button>

                        </div>


                        <div id="resultado_busqueda_comprobante"></div>


                        <div id="aviso_anulacion_origen" class="rounded-3 p-2 px-3 mb-2"
                            style="display: none; font-size: .78rem;">
                        </div>


                        <input type="hidden" id="referencia_venta_id" name="venta_referencia_id">

                    </div>



                    {{-- DATOS NUEVO COMPROBANTE --}}
                    <div class="mb-4">

                        <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">

                            <i class="bi bi-file-earmark-plus text-primary fs-5"></i>

                            2. Datos del nuevo comprobante

                        </h6>


                        <div class="row g-2 mb-3">

                            <div class="col-md-3">

                                <label class="form-label extra-small text-secondary fw-semibold"
                                    style="font-size: 0.75rem;">

                                    Tipo de comprobante destino

                                    <span class="text-danger">*</span>

                                </label>


                                <select id="tipo_comprobante_destino" name="tipo_comprobante_destino"
                                    class="form-select form-select-sm" onchange="actualizarSerieDestino()">

                                    @foreach ($tiposDocumento as $tipo)
                                        <option value="{{ $tipo->id }}">
                                            {{ $tipo->descripcion }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label extra-small text-secondary fw-semibold"
                                    style="font-size: 0.75rem;">

                                    Serie

                                    <span class="text-danger">*</span>

                                </label>


                                <input type="text" id="serie_destino" name="serie_destino"
                                    class="form-control form-control-sm bg-light" value="—" readonly>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label extra-small text-secondary fw-semibold"
                                    style="font-size: 0.75rem;">

                                    Número

                                    <span class="text-danger">*</span>

                                </label>


                                <div class="input-group input-group-sm">

                                    <input type="text" id="numero_destino" name="numero_destino"
                                        class="form-control bg-light" value="—" readonly>


                                    <span class="input-group-text bg-light text-muted">

                                        <i class="bi bi-info-circle"></i>

                                    </span>

                                </div>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label extra-small text-secondary fw-semibold"
                                    style="font-size: 0.75rem;">

                                    Fecha de emisión

                                    <span class="text-danger">*</span>

                                </label>


                                <input type="date" id="fecha_emision_destino" name="fecha_emision"
                                    class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">

                            </div>

                        </div>


                        <div class="bg-light bg-opacity-10 rounded-3 p-2 px-3 mb-3 d-flex align-items-center gap-2"
                            style="font-size: 0.75rem;">

                            <i class="bi bi-info-circle text-primary fs-6"></i>

                            <div>

                                <span class="text-dark d-block">

                                    Al emitir este comprobante se generará una referencia
                                    al documento original.

                                </span>

                                <span class="text-secondary fw-semibold">

                                    No se modificarán manualmente los datos del comprobante
                                    seleccionado.

                                </span>

                            </div>

                        </div>


                        <div class="border rounded-3 p-2 px-3 d-flex justify-content-between align-items-center">

                            <div class="small">

                                <span class="text-muted me-2" style="font-size: 0.75rem;">

                                    Documento de referencia

                                </span>


                                <strong class="text-dark" id="texto_documento_referencia">

                                    Ninguno seleccionado

                                </strong>

                            </div>

                        </div>

                    </div>



                    {{-- DETALLE --}}
                    <div>

                        <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">

                            <i class="bi bi-list-ul text-primary fs-5"></i>

                            3. Detalle

                        </h6>


                        <div class="bg-light bg-opacity-10 rounded-3 p-2 px-3 mb-3 d-flex align-items-center gap-2"
                            style="font-size: 0.75rem;">

                            <i class="bi bi-info-circle text-primary fs-6"></i>

                            <span class="text-secondary fw-semibold">

                                Se mantendrán los productos / servicios
                                del comprobante de referencia.

                            </span>

                        </div>


                        <div class="d-flex justify-content-end align-items-center gap-3">

                            <span class="text-muted small">
                                Total a emitir
                            </span>

                            <strong class="text-primary fs-4" id="total_a_emitir">

                                S/ 0.00

                            </strong>

                        </div>

                    </div>


                    {{-- ACCIONES --}}
                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <button type="button" class="btn btn-light btn-sm px-4 fw-semibold border"
                            onclick="volverOpciones()">

                            <i class="bi bi-arrow-left me-1"></i>

                            Volver

                        </button>


                        <button type="button" id="btnContinuarComprobanteExistente"
                            class="btn btn-primary btn-sm px-4 fw-semibold" onclick="continuarConversion()" disabled>

                            Continuar

                            <i class="bi bi-arrow-right ms-1"></i>

                        </button>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
