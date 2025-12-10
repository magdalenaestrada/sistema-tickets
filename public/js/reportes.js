let tabla = null;
let reporteSeleccionado = null;

function inicializarTabla() {
    if (!reporteSeleccionado) return;

    if (tabla) {
        tabla.destroy();
        $("#tablaReportes tbody").empty();
    }

    let thead = $("#tablaReportes thead tr");
    thead.empty();

    if (reporteSeleccionado === "ventas") {
        thead.append(
            "<th>Fecha</th><th>Tipo Documento</th><th>Vendedor</th><th>Cliente</th><th>Sucursal</th><th>Estado</th><th>Monto</th>"
        );
    } else if (reporteSeleccionado === "viajes") {
        thead.append(
            "<th>Fecha</th><th>Hora</th><th>Tipo Vehículo</th><th>Tipo Viaje</th><th>Origen</th><th>Destino</th><th>Costo</th>"
        );
    }

    let columnsConfig = [];

    if (reporteSeleccionado === "ventas") {
        columnsConfig = [
            { data: "fecha", name: "fecha" },
            { data: "descripcion", name: "descripcion" },
            { data: "vendedor", name: "vendedor" },
            { data: "cliente", name: "cliente" },
            { data: "sucursal", name: "sucursal" },
            {
                data: "estado",
                name: "estado",
                render: function (data) {
                    if (data === "E") return "Emitido";
                    if (data === "A") return "Anulado";
                    return data;
                },
            },
            { data: "monto", name: "monto" },
        ];
    } else if (reporteSeleccionado === "viajes") {
        columnsConfig = [
            { data: "fecha", name: "fecha" },
            { data: "hora", name: "hora" },
            { data: "tipo_vehiculo", name: "tipo_vehiculo" },
            { data: "tipo_viaje", name: "tipo_viaje" },
            { data: "origen", name: "origen" },
            { data: "destino", name: "destino" },
            { data: "costo", name: "costo" },
        ];
    }

    tabla = $("#tablaReportes").DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: route("reportes.data", { tipo: reporteSeleccionado }),
            data: function (d) {
                d.fecha_inicio = $("#fecha_inicio").val();
                d.fecha_fin = $("#fecha_fin").val();

                if (reporteSeleccionado === "ventas") {
                    d.tipo_documento = $("#tipo_documento").val();
                    d.cliente = $("#cliente").val();
                    d.vendedor = $("#vendedor").val();
                    d.sucursal = $("#sucursal").val();
                    d.estado = $("#estado").val();
                } else if (reporteSeleccionado === "viajes") {
                    d.tipo_vehiculo = $("#tipo_vehiculo").val();
                    d.tipo_viaje = $("#tipo_viaje").val();
                    d.punto_origen = $("#punto_origen").val();
                    d.punto_destino = $("#punto_destino").val();
                    d.hora_embarque = $("#hora_embarque").val();
                    d.fecha_salida = $("#fecha_salida_pasaje").val();
                    d.lunes = $("#lunes").is(":checked") ? 1 : 0;
                    d.martes = $("#martes").is(":checked") ? 1 : 0;
                    d.miercoles = $("#miercoles").is(":checked") ? 1 : 0;
                    d.jueves = $("#jueves").is(":checked") ? 1 : 0;
                    d.viernes = $("#viernes").is(":checked") ? 1 : 0;
                    d.sabado = $("#sabado").is(":checked") ? 1 : 0;
                    d.domingo = $("#domingo").is(":checked") ? 1 : 0;
                }
            },
        },
        columns: columnsConfig,
        pageLength: 25,
        dom: "Bfrtip",
        buttons: ["excelHtml5", "csvHtml5", "pdfHtml5"],
        order: [[0, "desc"]],
    });
}

function recargarTabla() {
    if (tabla) tabla.ajax.reload(null, false);
}

function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

$(".report-card").click(function () {
    $(".report-card").removeClass("selected");
    $(this).addClass("selected");

    reporteSeleccionado = $(this).data("reporte");
    $("#filtros-container").show();
    $(".filter-ventas").hide();
    $(".filter-pasajes").hide();

    if (reporteSeleccionado === "ventas") {
        $(".filter-ventas").show();
    } else if (reporteSeleccionado === "viajes") {
        $(".filter-pasajes").show();
    }
    inicializarTabla();
});

$("#cliente, #vendedor").on("input", debounce(recargarTabla, 300));
$("#tipo_documento, #sucursal, #fecha_inicio, #fecha_fin").on(
    "change",
    recargarTabla
);

$("#btnExcel").click(function (e) {
    e.preventDefault();
    if (tabla) tabla.button(".buttons-excel").trigger();
});

$("#btnPDF").click(function (e) {
    e.preventDefault();
    if (!reporteSeleccionado) return;

    let url = route("reportes.generar");
    let params = {
        tipo: reporteSeleccionado,
        fecha_inicio: $("#fecha_inicio").val(),
        fecha_fin: $("#fecha_fin").val(),
    };

    if (reporteSeleccionado === "ventas") {
        params.tipo_documento = $("#tipo_documento").val();
        params.cliente = $("#cliente").val();
        params.vendedor = $("#vendedor").val();
        params.sucursal = $("#sucursal").val();
        params.estado = $("#estado").val();
    } else if (reporteSeleccionado === "viajes") {
        params.tipo_vehiculo = $("#tipo_vehiculo").val();
        params.tipo_viaje = $("#tipo_viaje").val();
        params.punto_origen = $("#punto_origen").val();
        params.punto_destino = $("#punto_destino").val();
        params.hora_embarque = $("#hora_embarque").val();
        params.fecha_salida = $("#fecha_salida_pasaje").val();
        params.lunes = $("#lunes").is(":checked") ? 1 : 0;
        params.martes = $("#martes").is(":checked") ? 1 : 0;
        params.miercoles = $("#miercoles").is(":checked") ? 1 : 0;
        params.jueves = $("#jueves").is(":checked") ? 1 : 0;
        params.viernes = $("#viernes").is(":checked") ? 1 : 0;
        params.sabado = $("#sabado").is(":checked") ? 1 : 0;
        params.domingo = $("#domingo").is(":checked") ? 1 : 0;
    }

    let form = $("<form>", { action: url, method: "POST", target: "_blank" });

    let csrfToken = $('meta[name="csrf-token"]').attr("content");
    form.append(
        $("<input>", { type: "hidden", name: "_token", value: csrfToken })
    );

    $.each(params, function (key, value) {
        form.append($("<input>", { type: "hidden", name: key, value: value }));
    });

    $("body").append(form);
    form.submit();
    form.remove();
});
