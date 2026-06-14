$(".report-card").click(function () {
    $(".dashboard-report").hide();

    let reporte = $(this).data("reporte");

    if (reporte === "ventas") {
        $("#dashboardVentas").show();

        cargarDashboardVentas();
    }
});

function cargarDashboardVentas() {
    $.get(
        route("reportes.ventas.resumen"),
        {
            fecha_inicio: $("#fecha_inicio").val(),
            fecha_fin: $("#fecha_fin").val(),
            sucursal: $("#sucursal_general").val(),
        },
        function (r) {
            $("#totalVendido").text("S/ " + r.total_vendido);

            $("#totalComprobantes").text(r.comprobantes);

            $("#ticketPromedio").text("S/ " + r.ticket_promedio);

            $("#totalAnulados").text(r.anulados);
        },
    );
}
