<!-- Modal 2: Formulario Venta Rápida -->
<div class="modal fade" id="modalVentaRapida" tabindex="-1" aria-labelledby="modalVentaRapidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('facturacion.pos.store') }}"
            class="modal-content border-0 rounded-4 shadow-lg">
            @csrf

            <!-- Header -->
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light border rounded-circle"
                        onclick="cambiarModal('modalVentaRapida', 'modalOpcionesComprobante')" title="Volver">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h5 class="modal-title fw-bold text-dark fs-5" id="modalVentaRapidaLabel">Generar Comprobante</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">

                <!-- Sección 1: Datos Emisión -->
                <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Sucursal <span
                                    class="text-danger">*</span></label>
                            <select id="caja_id" name="caja_id" class="form-select form-select-sm" required>
                                @foreach ($cajas as $caja)
                                    <option value="{{ $caja->id }}" data-series='@json($caja->sucursal->serie->pluck('serie', 'tipo_documento_factura_id'))'>
                                        {{ $caja->sucursal->nombre_comercial }} —
                                        {{ $caja->usuario->persona->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Tipo documento <span
                                    class="text-danger">*</span></label>
                            <select id="tipo_documento_modal" name="tipo_documento_factura_id"
                                class="form-select form-select-sm" required>
                                <option value="">Seleccionar un tipo</option>
                                @foreach ($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->id }}">
                                        {{ $tipo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Serie <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="serie" class="form-control form-control-sm bg-white" readonly
                                required>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos del Cliente -->
                <div class="card border-0 border-start border-primary border-3 shadow-sm rounded-3 p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-3 small"><i class="bi bi-person me-1"></i> Datos del Cliente</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Documento <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="doc_cliente" name="documento" class="form-control"
                                    placeholder="DNI o RUC" required>
                                <button type="button" id="btnBuscarCliente" class="btn btn-primary"
                                    onclick="buscarCliente()">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label id="lblNombre" class="form-label small fw-semibold text-secondary">Nombres <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nombres" name="nombres" class="form-control form-control-sm"
                                required>
                        </div>

                        <div class="col-md-4" id="divApellidos">
                            <label class="form-label small fw-semibold text-secondary">Apellidos <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control form-control-sm"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Dirección</label>
                            <input type="text" id="direccion" name="direccion" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Detalle del Servicio -->
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <h6 class="fw-bold text-dark mb-3 small"><i class="bi bi-box-seam me-1"></i> Detalle del Servicio
                    </h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <select name="tipo_servicio_id" id="tipo_servicio_id" class="form-select form-select-sm">
                                <option value="1">Pasaje</option>
                                <option value="2">Encomienda</option>
                                <option value="3">Sobreequipaje</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="descripcion" class="form-control form-control-sm"
                                placeholder="Escribir descripción">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" id="unidad" class="form-control form-control-sm"
                                placeholder="Unidades">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" id="precio" class="form-control form-control-sm"
                                placeholder="Precio (incl. IGV)">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success btn-sm w-100 fw-bold"
                                onclick="agregarItem()">
                                <i class="bi bi-plus-lg"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle text-center mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th width="90">Tipo</th>
                                    <th>Descripción</th>
                                    <th width="90">Unidades</th>
                                    <th width="110">P. Unit. (c/IGV)</th>
                                    <th width="110">Valor s/IGV</th>
                                    <th width="90">IGV</th>
                                    <th width="110">Subtotal</th>
                                    <th width="60">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaItems" class="small"></tbody>
                        </table>
                    </div>

                    <input type="hidden" name="items" id="itemsInput">

                    <!-- Totales -->
                    <div class="d-flex justify-content-end mt-3">
                        <div class="bg-light p-3 rounded-3 text-end" style="min-width: 240px;">
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Subtotal:</span>
                                <span>S/ <span id="subtotal">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>IGV:</span>
                                <span>S/ <span id="igv">0.00</span></span>
                            </div>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between fw-bold text-dark fs-5">
                                <span>Total:</span>
                                <span>S/ <span id="total">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                    <i class="bi bi-check-circle me-1"></i> Generar y Emitir
                </button>
            </div>

        </form>
    </div>
</div>
