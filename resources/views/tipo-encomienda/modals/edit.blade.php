<!-- Modal Global -->
<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Tipo de Encomienda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formTipoEncomienda" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" value="{{ $tipo->descripcion }}" class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Precio Base</label>
                        <input type="number" step="0.01" name="precio_base" value="{{ $tipo->precio_base }}"
                            class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peso Límite (opcional)</label>
                        <input type="number" step="0.01" name="peso_limite" value="{{ $tipo->peso_limite }}"
                            class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Costo por Kg Extra (opcional)</label>
                        <input type="number" step="0.01" name="costo_kilo_extra"
                            value="{{ $tipo->costo_kilo_extra }}" class="form-control">
                    </div>

                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success">Guardar</button>
                </div>
            </form>

        </div>
    </div>
</div>
