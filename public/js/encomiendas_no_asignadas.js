$(function () {
    if (!$("#tablaEncomiendas").length || !$("#salidaAsignar").length) return;

    const csrf = $('meta[name="csrf-token"]').attr("content");
    let timer = null;

    const tsOrigen = new TomSelect("#filtroOrigen", {
        create: false,
        allowEmptyOption: true,
    });

    const tsDestino = new TomSelect("#filtroDestino", {
        create: false,
        allowEmptyOption: true,
    });

    const tsSalida = new TomSelect("#salidaAsignar", {
        create: false,
        allowEmptyOption: true,
    });

    const tabla = $("#tablaEncomiendas").DataTable({
        processing: true,
        serverSide: true,
        dom: "rtip",
        ajax: {
            url: route("encomiendas.datatable-no-asignadas"),
            data: function (d) {
                d.documento = $("#filtroDocumento").val();
                d.fecha = $("#filtroFecha").val();
                d.origen_id = $("#filtroOrigen").val();
                d.destino_id = $("#filtroDestino").val();
            },
        },
        columns: [
            { data: "checkbox", orderable: false, searchable: false },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    const api = $("#tablaEncomiendas").DataTable();
                    const info = api.page.info();

                    return info.recordsTotal - (info.start + meta.row);
                },
            },
            { data: "fecha" },
            { data: "emisor" },
            { data: "dni_emisor" },
            { data: "receptor" },
            { data: "dni_receptor" },
            { data: "origen" },
            { data: "destino" },
            { data: "total" },
            { data: "estado", orderable: false, searchable: false },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            if (window.lucide) lucide.createIcons();
        },
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último",
            },
        },
    });

    function debounceReload() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            tabla.ajax.reload(null, false);
            cargarSalidasDisponibles();
        }, 300);
    }

    function cargarSalidasDisponibles() {
        const origen = $("#filtroOrigen").val();
        const destino = $("#filtroDestino").val();

        tsSalida.clear();
        tsSalida.clearOptions();

        if (!origen || !destino) return;

        $.get(route("encomiendas.salidas-disponibles"), {
            origen_id: origen,
            destino_id: destino,
        }).done(function (resp) {
            tsSalida.addOptions(resp);
            tsSalida.refreshOptions(false);
        });
    }

    $("#filtroDocumento").on("input", function () {
        const valor = $(this).val().trim();
        if (valor.length > 0 && valor.length < 3) return;
        debounceReload();
    });

    $("#filtroFecha").on("change", debounceReload);
    $("#filtroOrigen").on("change", debounceReload);
    $("#filtroDestino").on("change", debounceReload);

    $("#btnLimpiar").on("click", function () {
        $("#filtroDocumento").val("");
        $("#filtroFecha").val("");
        tsOrigen.clear();
        tsDestino.clear();
        tsSalida.clear();
        tsSalida.clearOptions();
        $("#checkAll").prop("checked", false);
        tabla.ajax.reload(null, false);
    });

    $("#checkAll").on("change", function () {
        $(".check-encomienda").prop("checked", $(this).is(":checked"));
    });

    $("#tablaEncomiendas").on("change", ".check-encomienda", function () {
        if (!$(this).is(":checked")) {
            $("#checkAll").prop("checked", false);
        }
    });

    $("#btnAsignarSeleccionadas").on("click", function () {
        const salidaId = $("#salidaAsignar").val();

        if (!salidaId) {
            Swal.fire("Aviso", "Seleccione una salida.", "warning");
            return;
        }

        const ids = $(".check-encomienda:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        if (ids.length === 0) {
            Swal.fire(
                "Aviso",
                "Seleccione al menos una encomienda.",
                "warning",
            );
            return;
        }

        Swal.fire({
            icon: "question",
            title: "¿Asignar encomiendas?",
            text: "Se asignarán las encomiendas seleccionadas a la salida elegida.",
            showCancelButton: true,
            confirmButtonText: "Sí, asignar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: route("encomiendas.asignar-salida"),
                type: "POST",
                data: {
                    _token: csrf,
                    salida_id: salidaId,
                    encomienda_ids: ids,
                },
                success: function (resp) {
                    Swal.fire("Éxito", resp.message, "success");
                    $("#checkAll").prop("checked", false);
                    tabla.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const msg =
                        xhr.responseJSON?.message || "No se pudo asignar.";
                    Swal.fire("Error", msg, "error");
                },
            });
        });
    });

    $(document).on("click", ".imprimir", function () {
        const id = $(this).data("id");
        const url = route("encomiendas.ticket", id);
        const ventana = window.open(url, "_blank", "width=420,height=650");

        const interval = setInterval(function () {
            try {
                if (ventana.document.readyState === "complete") {
                    ventana.print();
                    clearInterval(interval);
                }
            } catch (e) {}
        }, 200);
    });

    $(document).on("click", ".editar", function () {
        const id = $(this).data("id");
        window.location.href = route("encomiendas.editar", id);
    });
});
