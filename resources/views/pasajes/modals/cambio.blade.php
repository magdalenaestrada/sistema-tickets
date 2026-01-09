<style>
    [id^="seat-"] {
        cursor: pointer;
        transition: transform 0.15s ease;
    }

    [id^="seat-"].seat-hover {
        transform: scale(1.05);
    }

    [id^="seat-"].seat-hover path,
    [id^="seat-"].seat-hover rect,
    [id^="seat-"].seat-hover polygon,
    [id^="seat-"].seat-hover circle {
        filter: brightness(0.85);
    }

    [id^="seat-"][data-estado="ocupado"],
    [id^="seat-"][data-estado="reservado"] {
        cursor: not-allowed !important;
    }

    [id^="seat-"][data-estado="ocupado"].seat-hover,
    [id^="seat-"][data-estado="reservado"].seat-hover {
        transform: none !important;
        filter: none !important;
    }

    [id^="seat-"].selected path,
    [id^="seat-"].selected rect,
    [id^="seat-"].selected polygon,
    [id^="seat-"].selected circle {
        fill: #0d6efd !important;
    }

    [id^="seat-"] path,
    [id^="seat-"] rect,
    [id^="seat-"] polygon,
    [id^="seat-"] circle {
        transition: fill 0.2s ease;
    }
</style>

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
                            <div class="col-md-2">
                                <label class="form-label">Fecha</label>
                                <input type="date" id="filtroFechaCambio" class="form-control">
                            </div>

                            <div class="col-md-2">
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

                            <div class="col-md-2">
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

                            <div class="col-md-3">
                                <label class="form-label">Tipo de viaje</label>
                                <select id="filtroTipoViajeCambio" class="form-select">
                                    <option value="">-- Todos --</option>
                                    @foreach ($tipos_viaje as $tv)
                                        <option value="{{ $tv->id }}">
                                            {{ $tv->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tipo de vehículo</label>
                                <select id="filtroTipoVehiculoCambio" class="form-select">
                                    <option value="">-- Todos --</option>
                                    @foreach ($tipos_vehiculos as $tv)
                                        <option value="{{ $tv->id }}">
                                            {{ $tv->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 d-flex align-items-end">
                                <button id="btnBuscarCambio" class="btn btn-primary w-100">
                                    <i class="link-icon"></i>
                                    Buscar horarios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="listaHorariosCambio" class="row g-3">
                    <div class="col-12">
                        <p class="text-center text-muted">
                            Use los filtros y haga clic en "Buscar horarios" para comenzar
                        </p>
                    </div>
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
