<div class="modal fade" id="modalDescuento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Cupón</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Formulario -->
            <form id="formDescuento" method="POST" action="{{ route('descuentos.guardar') }}">
                @csrf
                <input type="hidden" name="id" id="descuento_id">

                <div class="modal-body">

                    <h5 style="font-weight: bold">DATOS DEL CUPÓN</h5>
                    <hr class="border-gray-300 my-1">
                    <br>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_cupon_id" class="form-label">Tipo de cupón <span
                                    style="color: red">*</span></label>
                            <select class="form-select" name="tipo_cupon_id" id="tipo_cupon_id" required>
                                <option value="">Selecciona un tipo</option>
                                @foreach ($tipo_cupones as $tipo_cupon)
                                    <option value="{{ $tipo_cupon->id }}">
                                        {{ $tipo_cupon->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_descuento_id" class="form-label">Tipo de descuento <span
                                    style="color: red">*</span></label>
                            <select class="form-select" name="tipo_descuento_id" id="tipo_descuento_id" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="M">Monto fijo</option>
                                <option value="P">Porcentaje</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="codigo" class="form-label">Código <span style="color: red">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control" required>
                        </div>
                        <div class="col-md-6" id="descuento_monto_fijo" hidden>
                            <label for="monto_efectivo" class="form-label">Monto Descuento (S/) <span
                                    style="color: red">*</span></label>
                            <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo"
                                class="form-control">
                        </div>
                        <div class="col-md-6" id="descuento_porcentaje" hidden>
                            <label for="porcentaje" class="form-label">Porcentaje (%) <span
                                    style="color: red">*</span></label>
                            <input type="number" step="0.01" name="porcentaje" id="porcentaje" class="form-control"
                                min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cantidad_usos" class="form-label">Usos</label>
                            <input type="number" name="cantidad_usos" id="cantidad_usos" class="form-control"
                                min="0" step="1" inputmode="numeric"
                                onkeydown="return event.key !== '.' && event.key !== ','">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_maxima" class="form-label">Fecha límite de uso</label>
                            <input type="date" name="fecha_maxima" min="{{ $hoy }}" id="fecha_maxima" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tipo_asignacion_id" class="form-label">Tipo de asignación<span
                                    style="color: red">*</span></label>
                            <select class="form-select" name="tipo_asignacion_id" id="tipo_asignacion_id">
                                <option value="">Selecciona un tipo</option>
                                <option value="T">Todos los empleados</option>
                                <option value="G">Por cargos</option>
                                <option value="P">Personal</option>
                            </select>
                        </div>

                        <div class="col-md-9 mb-3" id="contenedor_empleados">
                            <label for="empleados_asignados" class="form-label">Seleccionar empleados <span
                                    style="color: red">*</span></label>
                            <select class="empleados_asignados" name="empleados_asignados[]" id="empleados_asignados"
                                multiple>
                                @foreach ($empleados as $empleado)
                                    <option value="{{ $empleado->id }}">{{ $empleado->persona->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-9 mb-3" id="cargos_asignados">
                            <label for="cargos_asignados" class="form-label">Seleccionar cargos<span
                                    style="color: red">*</span></label>
                            <select class="cargos_asignados" name="cargos_asignados[]" id="cargos_asignados"
                                multiple>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}"> Todos / {{ $cargo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
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
