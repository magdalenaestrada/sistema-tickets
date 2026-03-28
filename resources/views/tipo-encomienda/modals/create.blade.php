<!-- Modal Global -->
<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" id="modalContent">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Tipo de Encomienda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipoEncomienda" action="/tipo-encomienda" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Descripción <span style="color: red">*</span></label></label>
                        <input type="text" name="descripcion" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio Base (S/)</label></label>
                        <input type="number" step="0.01" name="precio_base" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>


        </div>
    </div>
</div>
