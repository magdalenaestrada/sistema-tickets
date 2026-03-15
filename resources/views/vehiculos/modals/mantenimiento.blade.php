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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Fecha inicio</label>
                                <input type="date" name="fecha_inicio" max="{{ $hoy }}" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Hora inicio</label>
                                <input type="time" name="hora_inicio" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="razon_id">Motivo de mantenimiento</label>
                                <select name="razon_id" id="razon_id" class="form-control">
                                    <option value="">Seleccionar un motivo</option>
                                    @foreach ($razones as $razon)
                                        <option value="{{ $razon->id }}">{{ $razon->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label for="descripcion">Observación</label>
                                <textarea name="descripcion" class="form-control"id="descripcion"></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="finMantenimiento" class="d-none">
                        <div class="mb-3">
                            <label>Fecha fin</label>
                            <input type="date" name="fecha_fin" max="{{ $hoy }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Hora fin</label>
                            <input type="time" name="hora_fin" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button> 
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
