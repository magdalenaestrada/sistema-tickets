<div class="modal fade" id="modalAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
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
                    <!-- Horario -->
                    <div class="mb-3">
                        <label for="horario_id" class="form-label">Horario</label>
                        <select name="horario_id" id="horario_id" class="form-select" required>
                            <option value="">Seleccione un horario</option>
                            @foreach ($horarios as $horario)
                                <option value="{{ $horario->id }}">
                                    {{ $horario->tipo_viaje->descripcion ?? '-' }}:
                                    {{ $horario->punto_origen->nombre_comercial ?? '-' }} →
                                    {{ $horario->punto_destino->nombre_comercial ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Primer Conductor -->
                    <div class="mb-3">
                        <label for="primer_conductor" class="form-label">Primer Conductor</label>
                        <select name="primer_conductor" id="primer_conductor" class="form-select" required>
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
                        <label for="segundo_conductor" class="form-label">Segundo Conductor</label>
                        <select name="segundo_conductor" id="segundo_conductor" class="form-select" disabled>
                            <option value="">Seleccione un conductor</option>
                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id }}">{{ $empleado->persona->nombres }}
                                    {{ $empleado->persona->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vehículo -->
                    <div class="mb-3">
                        <label for="vehiculo" class="form-label">Vehículo</label>
                        <select name="vehiculo" id="vehiculo" class="form-select">
                            <option value="">Seleccione un vehículo</option>
                            @foreach ($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}">{{ $vehiculo->numero_placa }} - {{ $vehiculo->tipo_vehiculo->descripcion }}
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
