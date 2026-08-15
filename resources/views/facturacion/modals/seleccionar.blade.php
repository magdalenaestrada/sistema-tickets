<!-- Modal 1: Opciones de Selección -->
<div class="modal fade" id="modalOpcionesComprobante" tabindex="-1" aria-labelledby="modalOpcionesComprobanteLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-3 shadow-lg">

            <!-- Header -->
            <div class="modal-header border-0 pb-0 align-items-start">
                <h5 class="modal-title fw-bold text-dark fs-5" id="modalOpcionesComprobanteLabel">Nuevo comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2">

                <!-- Stepper -->
                <div class="d-flex justify-content-between align-items-center my-3 px-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-primary d-flex align-items-center justify-content-center"
                            style="width: 28px; height: 28px;">1</span>
                        <span class="fw-semibold text-primary small">Seleccionar opción</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span
                            class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                            style="width: 28px; height: 28px;">2</span>
                        <span class="text-muted small">Completar información</span>
                    </div>
                    <div class="flex-grow-1 mx-3 border-top border-2 border-light-subtle"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span
                            class="badge rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                            style="width: 28px; height: 28px;">3</span>
                        <span class="text-muted small">Revisar y emitir</span>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <div class="text-center mb-4">
                    <h6 class="fw-bold text-dark fs-5 mb-1">¿Cómo deseas generar tu comprobante?</h6>
                    <p class="text-muted small">Selecciona una de las siguientes opciones para continuar.</p>
                </div>

                <div class="row g-3">

                    <!-- Tarjeta 1: Generar nuevo -->
                    <div class="col-md-6">
                        <div class="card h-100 border rounded-3 p-3 shadow-sm">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                                    <i class="bi bi-file-earmark-plus fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 small">Generar nuevo comprobante</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Crea un comprobante desde
                                        cero con los datos del cliente y productos.</p>
                                </div>
                            </div>

                            <ul class="list-unstyled my-3 text-secondary" style="font-size: 0.8rem;">
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Comprobante
                                    totalmente nuevo</li>
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Ingresa
                                    cliente y productos</li>
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Se generará
                                    un nuevo número</li>
                            </ul>

                            <!-- Abre modalVentaRapida y cierra este -->
                            <button type="button" class="btn btn-outline-success btn-sm w-100 mt-auto fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#modalVentaRapida">
                                Generar nuevo comprobante <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tarjeta 2: A partir de existente -->
                    <div class="col-md-6">
                        <div class="card h-100 border rounded-3 p-3 shadow-sm">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                                    <i class="bi bi-file-earmark-text fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 small">Generar a partir de existente</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.75rem;">Usa un comprobante ya emitido
                                        y conviértelo a otro tipo de documento.</p>
                                </div>
                            </div>

                            <ul class="list-unstyled my-3 text-secondary" style="font-size: 0.8rem;">
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-primary me-1"></i> Usa un
                                    comprobante como referencia</li>
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-primary me-1"></i> Convierte a
                                    otro tipo de comprobante</li>
                                <li class="mb-1"><i class="bi bi-check-circle-fill text-primary me-1"></i> Se
                                    mantendrán los datos del cliente y detalle</li>
                            </ul>
                            <!-- En la Tarjeta 2 del primer modal -->
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-auto fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#modalComprobanteExistente">
                                Usar comprobante existente <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
