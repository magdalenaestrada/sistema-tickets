let tabla = null;
let reporteSeleccionado = null;

function inicializarTabla() {
    if (!reporteSeleccionado) return;

    if (tabla) {
        tabla.destroy();
        $("#tablaReportes tbody").empty();
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
                d.tipo_documento = $("#tipo_documento").val();
                d.cliente = $("#cliente").val();
                d.vendedor = $("#vendedor").val();
                d.sucursal = $("#sucursal").val();
                d.estado = $("#estado").val();
            },
        },
        columns: [
            { data: "fecha", name: "fecha" },
            { data: "descripcion", name: "descripcion" },
            { data: "vendedor", name: "vendedor" },
            { data: "cliente", name: "cliente" },
            { data: "sucursal", name: "sucursal" },
            {
                data: "estado",
                name: "estado",
                render: function (data, type, row) {
                    if (data === "E") return "Emitido";
                    if (data === "A") return "Anulado";
                    return data;
                },
            },
            { data: "monto", name: "monto" },
        ],
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
    if (reporteSeleccionado === "ventas") $(".filter-ventas").show();

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
        tipo_documento: $("#tipo_documento").val(),
        cliente: $("#cliente").val(),
        vendedor: $("#vendedor").val(),
        sucursal: $("#sucursal").val(),
        estado: $("#estado").val(),
    };

    let form = $("<form>", { action: url, method: "POST", target: "_blank" });
    form.append("@csrf");
    $.each(params, function (key, value) {
        form.append($("<input>", { type: "hidden", name: key, value: value }));
    });
    $("body").append(form);
    form.submit();
    form.remove();
});
