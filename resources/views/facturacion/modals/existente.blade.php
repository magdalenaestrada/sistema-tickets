<!-- Modal 3: Usar Comprobante Existente -->
<div class="modal fade" id="modalComprobanteExistente" tabindex="-1" aria-labelledby="modalComprobanteExistenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-3 shadow-lg">
            
            <!-- Header -->
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark fs-5" id="modalComprobanteExistenteLabel">Nuevo comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2">
                
                <!-- Stepper en Paso 2 -->
                <div class="d-flex justify-content-between align-items-center my-3 px-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span class="text-primary small">Seleccionar opción</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-top border-2 border-primary"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                        <span class="fw-semibold text-primary small">Completar información</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">3</span>
                        <span class="text-muted small">Revisar y emitir</span>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <!-- Sección 1: Comprobante de referencia -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-text text-primary fs-5"></i> 1. Comprobante de referencia
                    </h6>

                    <label class="form-label small fw-semibold text-secondary">Buscar comprobante <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="buscar_comprobante_input" class="form-control border-start-0" placeholder="Ingrese serie y número o busque por cliente...">
                        <button type="button" class="btn btn-outline-primary px-3 fw-semibold" onclick="buscarComprobanteReferencia()">
                            Buscar
                        </button>
                    </div>

                    <!-- Resultado de la búsqueda / Tarjeta del Comprobante Encontrado -->
                    <div class="bg-light bg-opacity-10 border border-primary-subtle rounded-3 p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-light bg-opacity-20 text-primary px-2 py-1 rounded">Nota de venta</span>
                            <div>
                                <strong class="d-block text-dark small">CLIENTE VARIOS VARIOS</strong>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">DNI: 12345678</span>
                            </div>
                        </div>

                        <div class="text-center">
                            <span class="text-muted d-block extra-small" style="font-size: 0.7rem;">Fecha emisión</span>
                            <span class="fw-semibold text-dark small">07/07/2026</span>
                        </div>

                        <div class="text-end">
                            <span class="text-muted d-block extra-small" style="font-size: 0.7rem;">Total</span>
                            <strong class="text-dark fs-6">S/ 50.00</strong>
                        </div>

                        <div>
                            <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-semibold" style="font-size: 0.8rem;">
                                <i class="bi bi-eye me-1"></i> Ver detalle
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos del nuevo comprobante -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-plus text-primary fs-5"></i> 2. Datos del nuevo comprobante
                    </h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label extra-small text-secondary fw-semibold" style="font-size: 0.75rem;">Tipo de comprobante destino <span class="text-danger">*</span></label>
                            <select id="tipo_comprobante_destino" name="tipo_comprobante_destino" class="form-select form-select-sm">
                                <option value="boleta">Boleta (B)</option>
                                <option value="factura">Factura (F)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label extra-small text-secondary fw-semibold" style="font-size: 0.75rem;">Serie <span class="text-danger">*</span></label>
                            <input type="text" id="serie_destino" name="serie_destino" class="form-control form-control-sm bg-light" value="B003" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label extra-small text-secondary fw-semibold" style="font-size: 0.75rem;">Número <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="numero_destino" name="numero_destino" class="form-control bg-light" value="000456" readonly>
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-info-circle"></i></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label extra-small text-secondary fw-semibold" style="font-size: 0.75rem;">Fecha de emisión <span class="text-danger">*</span></label>
                            <input type="date" id="fecha_emision_destino" name="fecha_emision" class="form-control form-control-sm" value="2026-07-10">
                        </div>
                    </div>

                    <!-- Nota Informativa -->
                    <div class="bg-light bg-opacity-10 rounded-3 p-2.5 px-3 mb-3 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle text-primary fs-6"></i>
                        <div>
                            <span class="text-dark d-block">Al emitir este comprobante, se generará una referencia al documento original.</span>
                            <span class="text-secondary fw-semibold">No se modificará el comprobante de origen.</span>
                        </div>
                    </div>

                    <!-- Documento de referencia fijado -->
                    <div class="border rounded-3 p-2.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="small">
                            <span class="text-muted me-2" style="font-size: 0.75rem;">Documento de referencia</span>
                            <strong class="text-dark">Nota de venta N003-000123</strong>
                        </div>
                        <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-semibold" style="font-size: 0.8rem;">
                            <i class="bi bi-arrow-repeat me-1"></i> Cambiar referencia
                        </button>
                    </div>
                </div>

                <!-- Sección 3: Detalle -->
                <div>
                    <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul text-primary fs-5"></i> 3. Detalle
                    </h6>

                    <div class="bg-light bg-opacity-10 rounded-3 p-2.5 px-3 mb-3 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle text-primary fs-6"></i>
                        <span class="text-secondary fw-semibold">Se mantendrán los productos / servicios del comprobante de referencia.</span>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <span class="text-muted small">Total a emitir</span>
                        <strong class="text-primary fs-4">S/ 50.00</strong>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-top-0 pt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-sm px-4 fw-semibold border" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalOpcionesComprobante">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold">
                        Continuar <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>