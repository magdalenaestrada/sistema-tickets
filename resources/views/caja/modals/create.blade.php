<div class="modal fade" id="createCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar apertura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('caja.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="monto_apertura" class="form-label">Monto de apertura <span style="color: red">*</span></label></label>
                        <input type="number" step="0.01" name="monto_apertura" id="monto_apertura"
                            class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>

            </form>

        </div>
    </div>
</div>
