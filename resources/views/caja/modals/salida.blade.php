<div class="modal fade" id="salidaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('caja.salida', $caja) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Salida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Subtipo <span style="color: red">*</span></label></label>
                        <select name="subtipo_movimiento_caja_id" class="form-control" required>
                            @foreach ($subtiposSalida as $subtipo)
                                <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Método pago <span style="color: red">*</span></label></label>
                        <select name="metodo_pago_id" class="form-control" required>
                            @foreach ($metodosPago as $pago)
                                <option value="{{ $pago->id }}">{{ $pago->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Monto <span style="color: red">*</span></label></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
