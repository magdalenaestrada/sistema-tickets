<div class="modal fade" id="modalCambiarHorario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Cambiar horario y asiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- FILTROS --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Fecha</label>
                        <input type="date" id="modal_filtro_fecha" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Origen</label>
                        <select id="modal_filtro_origen" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach ($puntos_origen as $o)
                                <option value="{{ $o->id }}">{{ $o->nombre_comercial }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Destino</label>
                        <select id="modal_filtro_destino" class="form-control">
                            <option value="">-- Todos --</option>
                            @foreach ($puntos_destino as $d)
                                <option value="{{ $d->id }}">{{ $d->nombre_comercial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- HORARIOS + SVG --}}
                <div class="row">
                    <div class="col-md-8">
                        <div id="modal-horarios" class="row"></div>
                    </div>

                    <div class="col-md-4">
                        <div id="modal-svg-container">
                            <p class="text-muted">Seleccione un horario</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnGuardarCambioHorario" class="btn btn-primary" disabled>
                    Guardar cambios
                </button>
            </div>

        </div>
    </div>
</div>
