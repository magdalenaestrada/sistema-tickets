<div class="modal fade" id="modalAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formAsignacion">
            @csrf
            <input type="hidden" name="_method" id="method">
            <input type="hidden" name="id" id="asignacion_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Nueva Asignación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">FILTROS</h6>
                    <div class="row mt-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Punto de Origen</label>
                            <select id="filtro_origen" class="form-select">
                                <option value="">Elegir una opción</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">
                                        {{ $sucursal->nombre_comercial }}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Punto de Destino</label>
                            <select id="filtro_destino" class="form-select">
                                <option value="">Elegir una opción</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">
                                        {{ $sucursal->nombre_comercial }}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tipo de Viaje</label>
                            <select id="filtro_tipo_viaje" class="form-select">
                                <option value="">Elegir una opción</option>
                                @foreach ($tipo_viajes as $tipo_viaje)
                                    <option value="{{ $tipo_viaje->id }}">
                                        {{ $tipo_viaje->descripcion }}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tipo Vehículo</label>
                            <select id="filtro_tipo_vehiculo" class="form-select">

                                <option value="">Elegir una opción</option>
                                @foreach ($tipo_vehiculos as $tipo_vehiculo)
                                    <option value="{{ $tipo_vehiculo->id }}">
                                        {{ $tipo_vehiculo->descripcion }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de salida</label>
                            <input type="date" id="filtro_fecha" min="{{ $hoy }}" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <div class="row mt-3">
                        <h6 class="text-center">SELECCIONAR UN HORARIO Y CONDUCTOR</h6>
                        <div class="mb-3 mt-2">
                            <label for="horario_id" class="form-label">Horario <span style="color: red">*</span></label></label>
                            <select name="horario_id" id="horario_id" class="form-select" required>
                                <option value="">Seleccione un horario</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="primer_conductor_id" class="form-label">Primer Conductor <span style="color: red">*</span></label></label>
                            <select name="primer_conductor_id" id="primer_conductor_id" class="form-select" required>
                                <option value="">Seleccione un conductor</option>
                                @foreach ($empleados as $empleado)
                                    <option value="{{ $empleado->id }}">{{ $empleado->persona->nombres }}
                                        {{ $empleado->persona->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Segundo Conductor -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="otroConductorCheck">
                            <label class="form-check-label" for="otroConductorCheck">¿Otro conductor?</label>
                        </div>
                        <div class="mb-3">
                            <label for="segundo_conductor_id" class="form-label">Segundo Conductor</label>
                            <select name="segundo_conductor_id" id="segundo_conductor_id" class="form-select" disabled>
                                <option value="">Seleccione un conductor</option>
                                @foreach ($empleados as $empleado)
                                    <option value="{{ $empleado->id }}">{{ $empleado->persona->nombres }}
                                        {{ $empleado->persona->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="vehiculo" class="form-label">Vehículo <span style="color: red">*</span></label></label>
                            <select name="vehiculo" id="vehiculo" class="form-select" required>
                                <option value="">Seleccione un vehículo</option>
                                @foreach ($vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->id }}" data-tipo="{{ $vehiculo->tipo_vehiculo_id }}">
                                        {{ $vehiculo->numero_placa }} - {{ $vehiculo->tipo_vehiculo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
        </form>
    </div>
</div>
