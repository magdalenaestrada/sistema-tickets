<div class="modal fade" id="ingresoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('caja.ingreso', $caja) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Ingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Subtipo</label>
                        <select name="subtipo_movimiento_caja_id" class="form-control">
                            @foreach ($subtiposIngreso as $subtipo)
                                <option value="{{ $subtipo->id }}">{{ $subtipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Método pago</label>
                        <select name="metodo_pago_id" class="form-control">
                            @foreach ($metodosPago as $pago)
                                <option value="{{ $pago->id }}">{{ $pago->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
