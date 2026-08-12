<div class="modal fade" id="modalComprobanteExistente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('facturacion.pos.storeReferencia') }}" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf

            <!-- Header con Stepper Avanzado (Paso 2 activo) -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-white">
                <div>
                    <h4 class="modal-title fw-bold text-dark mb-3">Nuevo comprobante</h4>
                    
                    <!-- Stepper Visual -->
                    <div class="d-flex align-items-center gap-3 small text-muted">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                                <i class="bi bi-check"></i>
                            </span>
                            <span class="fw-semibold text-primary">Seleccionar opción</span>
                        </div>
                        <div class="border-top flex-grow-1 border-primary" style="width: 40px;"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px; height:24px;">2</span>
                            <span class="fw-semibold text-primary">Completar información</span>
                        </div>
                        <div class="border-top flex-grow-1" style="width: 40px;"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center" style="width:24px; height:24px;">3</span>
                            <span>Revisar y emitir</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                
                <!-- 1. Comprobante de referencia -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-receipt text-primary fs-5"></i> 1. Comprobante de referencia
                    </h6>
                    
                    <!-- Campo de búsqueda -->
                    <label class="form-label small text-muted fw-semibold">Buscar comprobante <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text bg-light-subtle border-light-subtle text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="buscar_comprobante" class="form-control border-light-subtle bg-light-subtle" placeholder="Ingrese serie y número o busque por cliente...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-primary w-100 fw-semibold" onclick="buscarComprobanteExistente()">
                                Buscar
                            </button>
                        </div>
                    </div>

                    <!-- Card de resultado encontrado -->
                    <div class="p-3 bg-primary-subtle bg-opacity-10 rounded-3 border border-primary-subtle d-flex align-items-center justify-content-between">
                        <div class="row w-100 align-items-center g-2 text-start">
                            <div class="col-md-2">
                                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill">Nota de venta</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block fs-7">Cliente</small>
                                <span class="fw-bold text-dark">CLIENTE VARIOS VARIOS</span>
                                <small class="text-muted d-block">DNI: 12345678</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block fs-7">Fecha emisión</small>
                                <span class="fw-semibold text-dark">07/07/2026</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <small class="text-muted d-block fs-7">Total</small>
                                <span class="fw-bold text-dark">S/ 50.00</span>
                            </div>
                        </div>
                        <div class="ps-3 border-start">
                            <a href="#" class="text-primary text-decoration-none small fw-semibold text-nowrap d-flex align-items-center gap-1">
                                <i class="bi bi-eye"></i> Ver detalle
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Datos del nuevo comprobante -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-file-earmark-plus text-primary fs-5"></i> 2. Datos del nuevo comprobante
                    </h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold">Tipo de comprobante destino <span class="text-danger">*</span></label>
                            <select id="tipo_documento_destino" name="tipo_documento_destino_id" class="form-select border-light-subtle bg-light-subtle" required>
                                <option value="1">Boleta (B)</option>
                                <option value="2">Factura (F)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold">Serie <span class="text-danger">*</span></label>
                            <input type="text" name="serie_destino" class="form-control border-light-subtle bg-light-subtle" value="B003" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold">Número <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="numero_destino" class="form-control border-light-subtle bg-light-subtle" value="000456" readonly>
                                <span class="input-group-text bg-light-subtle border-light-subtle text-muted" title="Número correlativo">
                                    <i class="bi bi-info-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-semibold">Fecha de emisión <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_emision" class="form-control border-light-subtle bg-light-subtle" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Alerta Informativa -->
                    <div class="alert alert-primary bg-primary-subtle border-0 text-primary-emphasis d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-3 small" role="alert">
                        <i class="bi bi-info-circle fs-6"></i>
                        <div>
                            Al emitir este comprobante, se generará una referencia al documento original. <br>
                            <strong>No se modificará</strong> el comprobante de origen.
                        </div>
                    </div>

                    <!-- Card Documento de referencia fijado -->
                    <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block fs-7">Documento de referencia</small>
                            <span class="fw-bold text-dark">Nota de venta N003-000123</span>
                        </div>
                        <button type="button" class="btn btn-link text-primary text-decoration-none small fw-semibold p-0 border-0 d-flex align-items-center gap-1">
                            <i class="bi bi-arrow-repeat"></i> Cambiar referencia
                        </button>
                    </div>
                </div>

                <!-- 3. Detalle -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-card-checklist text-primary fs-5"></i> 3. Detalle
                    </h6>

                    <div class="alert alert-primary bg-primary-subtle border-0 text-primary-emphasis d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-4 small" role="alert">
                        <i class="bi bi-info-circle fs-6"></i>
                        <span>Se mantendrán los productos / servicios del comprobante de referencia.</span>
                    </div>

                    <!-- Total a emitir -->
                    <div class="d-flex justify-content-end align-items-center gap-3 pe-2">
                        <span class="text-muted fw-semibold">Total a emitir</span>
                        <span class="fs-4 fw-bold text-primary">S/ 50.00</span>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 p-4 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3 fw-semibold">
                        &larr; Volver
                    </button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">
                        Continuar &rarr;
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>