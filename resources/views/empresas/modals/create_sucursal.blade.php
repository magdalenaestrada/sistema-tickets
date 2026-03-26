<div class="modal fade" id="modalSucursal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalTitulo">Registrar Sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formSucursal">
                <div class="modal-body">
                    <input type="hidden" id="sucursal_id" name="sucursal_id">
                    <div class="mb-3">
                        <label>Departamento <span style="color: red">*</span></label>
                        <select name="departamento_id" id="departamento_id" class="form-select" required>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Provincia <span style="color: red">*</span></label>
                        <select name="provincia_id" id="provincia_id" class="form-select" required>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Distrito <span style="color: red">*</span></label>
                        <select name="distrito_id" id="distrito_id" class="form-select" required>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Nombre Comercial <span style="color: red">*</span></label>
                        <input type="text" name="nombre_comercial" id="nombre_comercial_sucursal" class="form-control"
                            required>
                    </div>
                    <div class="mb-3">
                        <label>Dirección <span style="color: red">*</span></label>
                        <input type="text" name="direccion" id="direccion_sucursal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" maxlength="9"
                            pattern="\d{9}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
