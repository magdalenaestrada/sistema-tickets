<div class="modal fade" id="modalSolicitudAnulacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Solicitud de Anulación
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="venta_solicitud">
                <label class="form-label">
                    Motivo de la solicitud
                </label>
                <textarea id="motivo_solicitud" class="form-control" rows="4" placeholder="Ingrese el motivo..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button class="btn btn-warning" id="btnEnviarSolicitud">
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>
</div>
