<div class="modal fade" id="modalPuntos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Puntos y Tramos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formPunto">
                    @csrf
                    <input type="hidden" id="horario_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Origen</label>
                            <input type="text" id="origen_nombre" class="form-control" disabled>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Punto (Del ultimo al más reciente)</label>
                            <select class="form-select" name="destino_id" id="destino_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Costo desde origen (S/)</label>
                            <input type="number" step="0.01" name="costo_acumulado" id="costo_acumulado"
                                class="form-control" min="0" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Punto</button>
                </form>

                <hr>

                <!-- Tabla de puntos -->
                <table class="table table-striped" id="tablaPuntos">
                    <thead>
                        <tr>
                            <th>Origen</th>
                            <th>Punto</th>
                            <th>Costo desde origen</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
