<div class="modal fade" id="modalMantenimiento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloMantenimiento"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formMantenimiento">
                @csrf
                <input type="hidden" id="vehiculo_id">

                <div class="modal-body">
                    <div id="inicioMantenimiento">
                        <div class="mb-3">
                            <label>Fecha inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Hora inicio</label>
                            <input type="time" name="hora_inicio" class="form-control">
                        </div>
                    </div>

                    <div id="finMantenimiento" class="d-none">
                        <div class="mb-3">
                            <label>Fecha fin</label>
                            <input type="date" name="fecha_fin" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Hora fin</label>
                            <input type="time" name="hora_fin" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
