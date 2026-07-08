$(".report-card").on("click", function () {
    $(".dashboard-report").hide();

    const reporte = $(this).data("reporte");

    if (reporte === "ventas") {
        $("#dashboardVentas").show();
        cargarDashboardVentas();
    }
});

$(document).on(
    "change",
    `
    #fecha_inicio,
    #fecha_fin,
    #sucursal_general,
    #estado_general,
    #filtroVendedor,
    #filtroMetodoPago,
    #filtroTipoComprobante
`,
    function () {
        if ($("#dashboardVentas").is(":visible")) {
            cargarDashboardVentas();
        }
    },
);

$(document).on("keyup", "#filtroCliente", function () {
    if ($("#dashboardVentas").is(":visible")) {
        cargarDashboardVentas();
    }
});

function cargarResumenVentas(filtros) {
    $.get(route("reportes.ventas.resumen"), filtros, function (r) {
        $("#totalVendido").text("S/ " + r.total_vendido);
        $("#totalComprobantes").text(r.comprobantes);
        $("#ticketPromedio").text("S/ " + r.ticket_promedio);
        $("#totalAnulados").text(r.anulados);
    });
}

function obtenerFiltrosVentas() {
    return {
        fecha_inicio: $("#fecha_inicio").val(),
        fecha_fin: $("#fecha_fin").val(),
        sucursal: $("#sucursal_general").val(),

        estado: $("#estado_general").val(),
        vendedor: $("#filtroVendedor").val(),
        cliente: $("#filtroCliente").val(),
        metodo_pago: $("#filtroMetodoPago").val(),
        comprobante: $("#filtroTipoComprobante").val(),
    };
}

function cargarDashboardVentas() {
    let filtros = obtenerFiltrosVentas();

    cargarResumenVentas(filtros);
    cargarGraficoVentas(filtros);
    cargarTopSucursales(filtros);
    cargarTopVendedores(filtros);
    cargarTopClientes(filtros);

    $("#tablaVentas").DataTable().ajax.reload();
}
