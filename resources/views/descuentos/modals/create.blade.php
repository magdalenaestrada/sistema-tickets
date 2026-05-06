<div class="modal fade" id="modalDescuento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Cupón</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formDescuento" method="POST" action="{{ route('descuentos.guardar') }}">
                @csrf
                <input type="hidden" name="id" id="descuento_id">

                <div class="modal-body">

                    <h5 style="font-weight: bold">DATOS DEL CUPÓN</h5>
                    <hr class="border-gray-300 my-1"><br>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de cupón <span style="color:red">*</span></label>
                            <select class="form-select" name="tipo_cupon_id" id="tipo_cupon_id" required>
                                <option value="">Selecciona un tipo</option>
                                @foreach ($tipo_cupones as $tc)
                                    <option value="{{ $tc->id }}">{{ $tc->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de descuento <span style="color:red">*</span></label>
                            <select class="form-select" name="tipo_descuento_id" id="tipo_descuento_id" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="M">Monto fijo</option>
                                <option value="P">Porcentaje</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código <span style="color:red">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3" id="descuento_monto_fijo" hidden>
                            <label class="form-label">Monto Descuento (S/) <span style="color:red">*</span></label>
                            <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo"
                                class="form-control">
                        </div>

                        <div class="col-md-6 mb-3" id="descuento_porcentaje" hidden>
                            <label class="form-label">Porcentaje (%) <span style="color:red">*</span></label>
                            <input type="number" step="0.01" name="porcentaje" id="porcentaje" class="form-control"
                                min="0" max="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usos</label>
                            <input type="number" name="cantidad_usos" id="cantidad_usos" class="form-control"
                                min="0" step="1" inputmode="numeric"
                                onkeydown="return event.key !== '.' && event.key !== ','">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha límite de uso</label>
                            <input type="date" name="fecha_maxima" min="{{ $hoy }}" id="fecha_maxima"
                                class="form-control">
                        </div>
                    </div>

                    <h5 style="font-weight: bold">ASIGNACIÓN DEL CUPÓN</h5>
                    <hr class="border-gray-300 my-1">
                    <p class="text-muted small mt-2 mb-3">
                        Puedes combinar reglas. Sin reglas, el cupón aplica a todos.
                    </p>

                    <div id="contenedor_reglas"></div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1" id="btnAgregarRegla">
                        <i data-lucide="plus" style="width:14px;height:14px;vertical-align:middle;"></i>
                        Agregar regla
                    </button>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="templateRegla">
    <div class="regla-item border rounded p-3 mb-2 position-relative">
        <button type="button"
            class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 p-1 btnEliminarRegla"
            title="Quitar regla">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
        </button>
        <div class="row g-2 align-items-start">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Tipo</label>
                <select class="form-select form-select-sm select-tipo-regla" name="reglas_tipo[]">
                    <option value="T">Todos</option>
                    <option value="G">Por cargo</option>
                    <option value="P">Personas específicas</option>
                </select>
            </div>
            <div class="col-md-8 contenedor-detalle-regla">
                <div class="detalle-T">
                    <p class="text-muted small mt-4 mb-0">Aplica a todas las personas del sistema.</p>
                </div>
                <div class="detalle-G" style="display:none">
                    <label class="form-label small text-muted mb-1">Cargos</label>
                    <select class="select-cargos" name="cargos_asignados[]" multiple>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->id }}">{{ $cargo->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="detalle-P" style="display:none">
                    <label class="form-label small text-muted mb-1">Personas</label>
                    <select class="select-personas" name="personas_asignadas[]" multiple>
                        @foreach ($personas as $persona)
                            <option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>
