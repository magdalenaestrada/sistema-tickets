<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formHorario" method="POST" action="{{ route('horarios.guardar') }}">
                @csrf
                <input type="hidden" name="id" id="horario_id">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3"> <label for="tipo_viaje_id" class="form-label">Viaje <span style="color: red">*</span></label></label>
                            <select name="tipo_viaje_id" id="tipo_viaje_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($tiposViaje as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3"> <label for="tipo_vehiculo_id" class="form-label">Vehiculo <span style="color: red">*</span></label></label>
                            <select name="tipo_vehiculo_id" id="tipo_vehiculo_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($tipo_vehiculos as $tipo_vehiculo)
                                    <option value="{{ $tipo_vehiculo->id }}">{{ $tipo_vehiculo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 contenedor_costo_pasaje">
                            <label class="form-label">Costo pasaje <span style="color: red">*</span></label></label>
                            <input type="number" step="0.01" name="costo_pasaje" id="costo_pasaje"
                                class="form-control" required>
                        </div>
                        <div class="col-md-3"> <label class="form-label">Hora embarque <span style="color: red">*</span></label></label> <input type="time"
                                name="hora_salida" id="hora_salida" class="form-control" required> </div>
                        <div class="col-md-6"> <label class="form-label">Fecha salida <span style="color: red">*</span></label></label> <input type="date"
                                name="fecha_salida" id="fecha_salida" class="form-control" min="{{ $hoy }}"
                                required> </div>
                        <div class="col-md-6"> <label class="form-label">Repetir hasta</label> <input type="date"
                                name="repetir_hasta" id="repetir_hasta" class="form-control"> </div>
                    </div>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="punto_origen_id" class="form-label">Punto de origen <span style="color: red">*</span></label></label>
                            <select name="punto_origen_id" id="punto_origen_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 contenedor_destino">
                            <label for="punto_destino_id" class="form-label">Punto de destino <span style="color: red">*</span></label></label>
                            <select name="punto_destino_id" id="punto_destino_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-12"> <label class="form-label">Días de repetición</label>
                            <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox"
                                    name="lunes" id="lunes"> <label class="form-check-label"
                                    for="lunes">Lunes</label>
                            </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox"
                                    name="martes" id="martes"> <label class="form-check-label"
                                    for="martes">Martes</label>
                            </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input" type="checkbox"
                                    name="miercoles" id="miercoles"> <label class="form-check-label"
                                    for="miercoles">Miércoles</label> </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input"
                                    type="checkbox" name="jueves" id="jueves"> <label class="form-check-label"
                                    for="jueves">Jueves</label>
                            </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input"
                                    type="checkbox" name="viernes" id="viernes"> <label class="form-check-label"
                                    for="viernes">Viernes</label>
                            </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input"
                                    type="checkbox" name="sabado" id="sabado"> <label class="form-check-label"
                                    for="sabado">Sábado</label>
                            </div>
                            <div class="form-check form-check-inline"> <input class="form-check-input"
                                    type="checkbox" name="domingo" id="domingo"> <label class="form-check-label"
                                    for="domingo">Domingo</label> </div>
                        </div>
                    </div>
                    <hr>
                    <div id="contenedorPuntos" class="mt-4 d-none">
                        <h6 class="fw-bold mb-3">PUNTOS Y TRAMOS</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Origen</label>
                                <input type="text" id="origen_nombre" class="form-control" disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Punto</label>
                                <select class="form-select" id="punto_destino">
                                    <option value="">Seleccione</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Costo</label>
                                <input type="number" step="0.01" id="costo_tramo" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Duración (min)</label>
                                <input type="number" id="duracion_tramo" class="form-control" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-success" id="btnAgregarPunto">+</button>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table" id="tablaPuntos">
                                <thead>
                                    <tr>
                                        <th>Origen</th>
                                        <th>Punto</th>
                                        <th>Costo</th>
                                        <th>Tiempo</th>
                                        <th>Hora llegada</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="inputsPuntos"></div>

                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button> <button type="submit"
                        class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
