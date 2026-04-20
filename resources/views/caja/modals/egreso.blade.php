<div class="modal fade" id="modalSalida" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form-salida" action="{{ route('caja.salida', $caja->id) }}" method="POST"
            class="modal-content border-0 shadow">
            @csrf

            {{-- Campos reales que espera backend --}}
            <input type="hidden" name="metodo_pago_id" id="metodo_pago_id_real">
            <input type="hidden" name="amount" id="amount_real">
            <input type="hidden" name="monto_efectivo" id="monto_efectivo_real">
            <input type="hidden" name="monto_digital" id="monto_digital_real">
            <input type="hidden" name="billetera_digital_id" id="billetera_digital_id_real">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title w-100 text-center">Registrar egreso</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Subtipo</label>
                        <select name="subtipo_movimiento_caja_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($subtiposSalida as $subtipo)
                                <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small mb-1">Agregar método de pago</label>
                        <select id="tipo_salida_visual" class="form-select" required>
                            <option value="">Seleccione</option>
                            <option value="contado">Contado</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="mixto">Pago Mixto</option>
                        </select>
                    </div>

                    <div id="bloque_metodos_salida" class="col-12 d-none">
                        <div class="row g-2">

                            <div class="col-12 metodo-salida-row d-none" id="row_contado">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <div class="metodo-box">💵 Contado</div>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="number" step="0.01" min="0" id="input_contado"
                                            class="form-control metodo-input" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 metodo-salida-row d-none" id="row_tarjeta">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <div class="metodo-box">💳 Tarjeta</div>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="number" step="0.01" min="0" id="input_tarjeta"
                                            class="form-control metodo-input" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 metodo-salida-row d-none" id="row_yape">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <div class="metodo-box">🟪 Yape</div>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="number" step="0.01" min="0" id="input_yape"
                                            class="form-control metodo-input" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 metodo-salida-row d-none" id="row_plin">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <div class="metodo-box">🟦 Plin</div>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="number" step="0.01" min="0" id="input_plin"
                                            class="form-control metodo-input" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 metodo-salida-row d-none" id="row_transferencia">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <div class="metodo-box">🏦 Transferencia</div>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="number" step="0.01" min="0" id="input_transferencia"
                                            class="form-control metodo-input" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small mb-1">Observaciones</label>
                        <input type="text" name="description" class="form-control" placeholder="Ej: Te la debo">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Registrar egreso</button>
            </div>
        </form>
    </div>
</div>
