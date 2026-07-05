<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">

                <div class="text-center mb-4">
                    <h2 class="fw-bold" style="color:#7c83ff;">Total a pagar:</h2>
                    <h1 class="fw-bold">S/. <span id="modal_total_pagar">0.00</span></h1>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Agregar método de pago</label>
                            <select id="modal_metodo_pago" class="form-select">
                                <option value="1">Pago Efectivo</option>
                                <option value="2">Pago Digital</option>
                                <option value="3">Pago Mixto</option>
                            </select>
                        </div>

                        <div class="alert alert-warning py-2 text-center mb-3 d-none" id="alerta_pago">
                            No coincide con el total a pagar
                        </div>

                        <div class="row g-2 mb-2" id="modal_efectivo_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    💵 Contado
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="modal_pago_efectivo" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <div class="row g-2 mb-2" id="modal_tarjeta_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    💳 Tarjeta débito 
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="modal_pago_tarjeta" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <div class="row g-2 mb-2" id="modal_yape_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    📱 Yape
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="modal_pago_yape" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <div class="row g-2 mb-2" id="modal_plin_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    📲 Plin
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="modal_pago_plin" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <div class="row g-2 mb-4" id="modal_transferencia_div">
                            <div class="col-6">
                                <label class="form-control bg-body-secondary fw-bold">
                                    🏦 Transferencia
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" id="modal_pago_transferencia" class="form-control"
                                    value="0">
                            </div>
                        </div>

                        <input type="hidden" name="metodo_pago_id" id="metodo_pago_id">
                        <input type="hidden" name="pago_efectivo" id="pago_efectivo">
                        <input type="hidden" name="pago_tarjeta" id="pago_tarjeta">
                        <input type="hidden" name="pago_yape" id="pago_yape">
                        <input type="hidden" name="pago_plin" id="pago_plin">
                        <input type="hidden" name="pago_transferencia" id="pago_transferencia">

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-success w-100" id="btnConfirmarVenta">
                                    Terminar Venta
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
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
