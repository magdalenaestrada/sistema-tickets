<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Aquí va tu form -->
            <form id="formHorario" method="POST" action="{{ route('horarios.guardar') }}">
                @csrf
                <input type="hidden" name="id" id="horario_id">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"> <label for="tipo_viaje_id" class="form-label">Viaje</label>
                            <select name="tipo_viaje_id" id="tipo_viaje_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($tiposViaje as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"> <label for="tipo_vehiculo_id" class="form-label">Vehiculo</label>
                            <select name="tipo_vehiculo_id" id="tipo_vehiculo_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($tipo_vehiculos as $tipo_vehiculo)
                                    <option value="{{ $tipo_vehiculo->id }}">{{ $tipo_vehiculo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 contenedor_costo_pasaje">
                            <label class="form-label">Costo pasaje</label>
                            <input type="number" step="0.01" name="costo_pasaje" id="costo_pasaje"
                                class="form-control" required>
                        </div>
                        <div class="col-md-4"> <label class="form-label">Hora embarque</label> <input type="time"
                                name="hora_embarque" id="hora_embarque" class="form-control" required> </div>
                        <div class="col-md-4"> <label class="form-label">Fecha salida</label> <input type="date"
                                name="fecha_salida" id="fecha_salida" class="form-control" required> </div>
                        <div class="col-md-4"> <label class="form-label">Repetir hasta</label> <input type="date"
                                name="repetir_hasta" id="repetir_hasta" class="form-control"> </div>
                    </div>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="punto_origen_id" class="form-label">Punto de origen</label>
                            <select name="punto_origen_id" id="punto_origen_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 contenedor_destino">
                            <label for="punto_destino_id" class="form-label">Punto de destino</label>
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
                </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button> <button type="submit"
                        class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
