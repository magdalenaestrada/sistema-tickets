<div class="modal fade" id="modalAnulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">
                <input type="hidden" id="venta_id_anular" value="">

                <div class="text-center mb-4">
                    <h2 class="fw-bold text-danger">Total a devolver:</h2>
                    <h1 class="fw-bold">
                        S/. <span id="modal_total_devolver">0.00</span>
                    </h1>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Caja</label>
                            <select id="caja_anulacion_id" class="form-select" name="caja_anulacion_id">
                                @foreach ($cajasAbiertas as $c)
                                    <option value="{{ $c->id }}">
                                        Caja {{ $c->sucursal->nombre_comercial }} - {{ $c->usuario?->persona->nombre_completo ?? 'Sin usuario' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Motivo de la anulación
                            </label>

                            <textarea id="motivo_anulacion" class="form-control" rows="3" maxlength="255" placeholder="Ingrese el motivo..."
                                name="motivo"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Método de devolución
                            </label>

                            <select id="modal_metodo_devolucion" class="form-select">
                                <option value="1">Efectivo</option>
                                <option value="2">Digital</option>
                                <option value="3">Mixto</option>
                            </select>
                        </div>

                        <div class="alert alert-warning py-2 text-center mb-3 d-none" id="alerta_devolucion">
                            No coincide con el total a devolver
                        </div>

                        {{-- TARJETA --}}
                        <div class="row g-2 mb-2" id="devolucion_tarjeta_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    💳 Tarjeta
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="devolucion_tarjeta" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        {{-- YAPE --}}
                        <div class="row g-2 mb-2" id="devolucion_yape_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    📱 Yape
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="devolucion_yape" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        {{-- PLIN --}}
                        <div class="row g-2 mb-2" id="devolucion_plin_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    📲 Plin
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="devolucion_plin" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        {{-- TRANSFERENCIA --}}
                        <div class="row g-2 mb-2" id="devolucion_transferencia_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    🏦 Transferencia
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="devolucion_transferencia" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        {{-- EFECTIVO --}}
                        <div class="row g-2 mb-4" id="devolucion_efectivo_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    💵 Efectivo
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="devolucion_efectivo" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <div class="row g-2">

                            <div class="col-6">
                                <button type="button" class="btn btn-danger w-100" id="btnConfirmarAnulacion">
                                    Confirmar anulación
                                </button>
                            </div>

                            <div class="col-6">
                                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
