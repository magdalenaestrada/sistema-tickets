<div class="modal fade" id="modalCambioHorario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Cambiar asiento / horario</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" id="filtroFechaCambio" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Origen</label>
                                <select id="filtroOrigenCambio" class="form-select">
                                    <option value="">-- Origen --</option>
                                    @foreach ($puntos_origen as $origen)
                                        <option value="{{ $origen->id }}">
                                            {{ $origen->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Destino</label>
                                <select id="filtroDestinoCambio" class="form-select">
                                    <option value="">-- Destino --</option>
                                    @foreach ($puntos_destino as $destino)
                                        <option value="{{ $destino->id }}">
                                            {{ $destino->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button id="btnBuscarCambio" class="btn btn-primary w-100">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="listaHorariosCambio" class="row g-3"></div>

                <hr>

                <div id="contenedorAsientosCambio" class="d-none">
                    <h5>Seleccione asiento</h5>
                    <div id="svgBusCambio"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" onclick="confirmarCambioHorario()">
                    Confirmar cambio
                </button>
            </div>

        </div>
    </div>
</div>
