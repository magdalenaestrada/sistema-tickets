<div id="filtros-container" class="mb-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row g-3 align-items-end">

                {{-- PERIODO --}}
                <div class="col-md-2">
                    <label class="form-label">Periodo</label>
                    <select id="periodo" class="form-select">
                        <option value="hoy">Hoy</option>
                        <option value="semana">Esta Semana</option>
                        <option value="mes" selected>Este Mes</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>

                {{-- FECHAS --}}
                <div id="rangoPersonalizado" class="col-md-4" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" id="fecha_fin" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- SUCURSAL --}}
                <div class="col-md-3">
                    <label class="form-label">Sucursal</label>

                    <select id="sucursal_general" class="form-select">

                        <option value="">
                            Todas las sucursales
                        </option>

                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">
                                {{ $sucursal->nombre_comercial }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- BOTONES --}}
                <div class="col-md-3">

                    <div class="d-flex gap-2">

                        <button id="btnBuscar" class="btn btn-primary">

                            Buscar

                        </button>

                        <button id="btnExcel" class="btn btn-success">

                            Excel

                        </button>

                        <button id="btnPDF" class="btn btn-danger">

                            PDF

                        </button>

                    </div>

                </div>

            </div>

            <hr>

            {{-- FILTROS ESPECIFICOS --}}
            <div id="filtrosVentas" style="display:none;">

                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">
                            Tipo Documento
                        </label>

                        <select id="tipo_documento" class="form-select">

                            <option value="">
                                Todos
                            </option>

                            @foreach ($tipos_documento as $tipo)
                                <option value="{{ $tipo->id }}">
                                    {{ $tipo->descripcion }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">
                            Estado
                        </label>

                        <select id="estado" class="form-select">

                            <option value="">
                                Todos
                            </option>

                            <option value="E">
                                Emitidos
                            </option>

                            <option value="A">
                                Anulados
                            </option>

                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Cliente
                        </label>

                        <input type="text" id="cliente" class="form-control" placeholder="Buscar cliente">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Vendedor
                        </label>

                        <input type="text" id="vendedor" class="form-control" placeholder="Buscar vendedor">
                    </div>

                </div>

            </div>

            <div id="filtrosPasajeros" style="display:none;"></div>

            <div id="filtrosViajes" style="display:none;"></div>

            <div id="filtrosEncomiendas" style="display:none;"></div>

            <div id="filtrosCupones" style="display:none;"></div>

            <div id="filtrosVehiculos" style="display:none;"></div>

        </div>
    </div>

</div>
