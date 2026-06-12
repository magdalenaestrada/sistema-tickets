<div class="modal fade" id="modalSalida" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form-salida" action="{{ route('caja.salida', $caja->id) }}" method="POST"
            class="modal-content border-0 shadow">
            @csrf

            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h3 class="mb-1 text-primary fw-light">Registrar salida</h3>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2 px-4">
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
                        <select name="metodo_pago_id" id="tipo_salida" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}">{{ $metodo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SIMPLE --}}
                    <div class="col-12 salida-campo d-none" id="salida_monto_simple">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="bg-light rounded p-3 fw-semibold">
                                    💵 Monto
                                </div>
                            </div>
                            <div class="col-md-7">
                                <input type="number" step="0.01" min="0.01" name="amount"
                                    class="form-control form-control-lg" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    {{-- MIXTO --}}
                    <div class="col-12 salida-campo d-none" id="salida_monto_efectivo">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="bg-light rounded p-3 fw-semibold">
                                    💵 Efectivo
                                </div>
                            </div>
                            <div class="col-md-7">
                                <input type="number" step="0.01" min="0.01" name="monto_efectivo"
                                    class="form-control form-control-lg" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 salida-campo d-none" id="salida_monto_digital">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="bg-light rounded p-3 fw-semibold">
                                    📲 Monto digital
                                </div>
                            </div>
                            <div class="col-md-7">
                                <input type="number" step="0.01" min="0.01" name="monto_digital"
                                    class="form-control form-control-lg" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 salida-campo d-none" id="salida_billetera">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="bg-light rounded p-3 fw-semibold">
                                    🏦 Billetera / digital
                                </div>
                            </div>
                            <div class="col-md-7">
                                <select name="billetera_digital_id" class="form-select form-select-lg">
                                    <option value="">Seleccione</option>
                                    @foreach ($billeterasDigitales as $billetera)
                                        <option value="{{ $billetera->id }}">{{ $billetera->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small mb-1">Observaciones</label>
                        <input type="text" name="description" class="form-control">
                    </div>

                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success px-4">
                    Registrar salida
                </button>
            </div>
        </form>
    </div>
</div>
