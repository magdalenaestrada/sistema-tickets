<div id="dashboardVentas" class="dashboard-report" style="display:none;">

    <div class="row mb-3">

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6>Total Vendido</h6>
                    <h3 id="totalVendido">S/ 0.00</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6>Comprobantes</h6>
                    <h3 id="totalComprobantes">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6>Ticket Promedio</h6>
                    <h3 id="ticketPromedio">S/ 0.00</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6>Anulados</h6>
                    <h3 id="totalAnulados">0</h3>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-3">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Ventas por Día
                </div>

                <div class="card-body">
                    <canvas id="graficoVentas"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-3">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Ventas por Sucursal
                </div>

                <div class="card-body">
                    <div id="ventasSucursal"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Top Vendedores
                </div>

                <div class="card-body">
                    <div id="topVendedores"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-3">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Top Clientes
                </div>

                <div class="card-body">
                    <div id="topClientes"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header">
            Detalle de Ventas
        </div>

        <div class="card-body">

            <table
                id="tablaVentas"
                class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody></tbody>

            </table>

        </div>
    </div>

</div>