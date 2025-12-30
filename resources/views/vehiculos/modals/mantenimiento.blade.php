<div class="modal fade" id="modalMantenimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Inicio de mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMantenimiento" method="POST">
                <input type="hidden" name="vehiculo_id" id="vehiculo_id">

                @php
                    $hoy = \Carbon\Carbon::now('America/Lima')->format('Y-m-d');
                    $ahora = \Carbon\Carbon::now('America/Lima')->format('H:i');
                @endphp

                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <label for="fecha_inicio" class="form-label">Fecha</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                value="{{ $hoy }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control"
                                value="{{ $ahora }}" required>
                        </div>
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
