<div class="modal fade" id="modalObservacion" tabindex="-1"
    aria-labelledby="modalObservacionLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <form id="formObservacion" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="modalObservacionLabel">
                        <i class="fas fa-comment-alt text-primary me-2"></i>
                        Observación del movimiento
                    </h5>
                    <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-light border small">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        Esta observación es interna. No modifica el monto
                        ni el método de pago registrado.
                    </div>

                    <label class="form-label fw-semibold">
                        Comentario
                    </label>

                    <textarea
                        name="observacion"
                        id="observacionTexto"
                        class="form-control"
                        rows="4"
                        maxlength="1000"
                        placeholder="Ej. Este ingreso fue realizado por Plin, pero se registró como Yape..."
                        required></textarea>

                    <small class="text-muted">
                        Máximo 1000 caracteres.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Guardar observación
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>