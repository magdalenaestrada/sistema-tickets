<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-m modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Vehiculo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formVehiculo" method="POST" action="{{ route('vehiculos.guardar') }}">

                @csrf
                <input type="hidden" id="vehiculo_id" name="vehiculo_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo_vehiculo_id" class="form-label">Tipo de vehículo <span
                                style="color: red">*</span></label></label>
                        <select name="tipo_vehiculo_id" id="tipo_vehiculo_id" class="form-select" required>
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Placa <span style="color: red">*</span></label>
                        <input type="text" name="numero_placa" id="numero_placa" class="form-control"
                            placeholder="ABC-123 / 12A-ABC / D0M-1T3" maxlength="7"
                            pattern="^(?=.{7}$)[A-Za-z0-9]{3}-[A-Za-z0-9]{3}$"
                            title="Ingrese una placa válida en formato XXX-XXX, por ejemplo: ABC-123, 12A-ABC o D0M-1T3"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marca</label></label>
                        <input type="text" name="marca" id="marca" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Habilitación vehicular <span
                                style="color: red">*</span></label></label>
                        <input type="text" name="habilitacion_vehicular" id="habilitacion_vehicular"
                            class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
