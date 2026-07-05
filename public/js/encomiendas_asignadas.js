$(function () {
    if (!$("#tablaEncomiendas").length) return;

    const csrf = $('meta[name="csrf-token"]').attr("content");
    let timer = null;

    if ($("#filtroOrigen").length) {
        new TomSelect("#filtroOrigen", {
            create: false,
            allowEmptyOption: true,
        });
    }

    if ($("#filtroDestino").length) {
        new TomSelect("#filtroDestino", {
            create: false,
            allowEmptyOption: true,
        });
    }

    if ($("#filtroSalida").length) {
        new TomSelect("#filtroSalida", {
            create: false,
            allowEmptyOption: true,
        });
    }

    const tabla = $("#tablaEncomiendas").DataTable({
        processing: true,
        serverSide: true,
        dom: "rtip",
        info: false,
        ajax: {
            url: route("encomiendas.datatable-asignadas"),
            data: function (d) {
                d.documento = $("#filtroDocumento").val();
                d.origen_id = $("#filtroOrigen").length
                    ? $("#filtroOrigen").val()
                    : "";
                d.destino_id = $("#filtroDestino").length
                    ? $("#filtroDestino").val()
                    : "";
                d.salida_id = $("#filtroSalida").length
                    ? $("#filtroSalida").val()
                    : "";
            },
            error: function (xhr) {
                console.error("Error DataTable:", xhr.responseText);
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
            { data: "fecha", name: "fecha" },
            { data: "receptor", name: "receptor" },
            { data: "dni_receptor", name: "dni_receptor" },
            { data: "origen", name: "origen" },
            { data: "destino", name: "destino" },
            { data: "salida", name: "salida" },
            { data: "estado", name: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            if (window.lucide) lucide.createIcons();
            actualizarContador();
            $("#checkAll").prop("checked", false);
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
        }, 300);
    }

    function actualizarContador() {
        const seleccionados = $(".check-llegada:checked").length;
        $("#contadorSeleccionados").text(`${seleccionados} seleccionadas`);
    }

    $("#filtroDocumento").on("input", function () {
        const valor = $(this).val().trim();
        if (valor.length > 0 && valor.length < 3) return;
        debounceReload();
    });

    $("#filtroOrigen").on("change", debounceReload);
    $("#filtroDestino").on("change", debounceReload);
    $("#filtroSalida").on("change", debounceReload);

    $("#btnLimpiar").on("click", function () {
        $("#filtroDocumento").val("");

        if ($("#filtroOrigen").length && $("#filtroOrigen")[0].tomselect) {
            $("#filtroOrigen")[0].tomselect.clear();
        }

        if ($("#filtroDestino").length && $("#filtroDestino")[0].tomselect) {
            $("#filtroDestino")[0].tomselect.clear();
        }

        if ($("#filtroSalida").length && $("#filtroSalida")[0].tomselect) {
            $("#filtroSalida")[0].tomselect.clear();
        }

        $("#checkAll").prop("checked", false);
        tabla.ajax.reload(null, false);
    });

    $("#checkAll").on("change", function () {
        $(".check-llegada").prop("checked", this.checked);
        actualizarContador();
    });

    $(document).on("change", ".check-llegada", function () {
        const total = $(".check-llegada").length;
        const seleccionados = $(".check-llegada:checked").length;
        $("#checkAll").prop("checked", total > 0 && total === seleccionados);
        actualizarContador();
    });

    $("#btnConfirmarLlegada").on("click", function () {
        const ids = $(".check-llegada:checked")
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
            title: "¿Confirmar llegada?",
            text: "Se actualizará el estado de las encomiendas seleccionadas.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, confirmar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: route("encomiendas.entregar-masivo"),
                type: "POST",
                data: {
                    _token: csrf,
                    ids: ids,
                },
                success: function (data) {
                    Swal.fire("Éxito", data.message, "success");
                    $("#checkAll").prop("checked", false);
                    actualizarContador();
                    tabla.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const msg =
                        xhr.responseJSON?.message ||
                        "Error al confirmar llegada";
                    Swal.fire("Error", msg, "error");
                },
            });
        });
    });

    $(document).on("click", ".entregar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Confirmar entrega?",
            text: "La encomienda será marcada como entregada.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, confirmar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: route("encomiendas.entregar", id),
                type: "POST",
                data: {
                    _token: csrf,
                },
                success: function (res) {
                    Swal.fire("Éxito", res.message, "success");
                    tabla.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire(
                        "Error",
                        xhr.responseJSON?.message || "No se pudo entregar",
                        "error",
                    );
                },
            });
        });
    });

    $(document).on("click", ".enagencia", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Confirmar que llegó a agencia?",
            text: "La encomienda será marcada como en agencia.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, confirmar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: route("encomiendas.agencia", id),
                type: "POST",
                data: {
                    _token: csrf,
                },
                success: function (res) {
                    Swal.fire("Éxito", res.message, "success");
                    tabla.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire(
                        "Error",
                        xhr.responseJSON?.message || "No se pudo enviar a agencia",
                        "error",
                    );
                },
            });
        });
    });

    $(document).on("click", ".imprimir", function () {
        const id = $(this).data("id");
        const url = route("encomiendas.ticket", id);

        let iframe = document.getElementById("printFrame");

        if (!iframe) {
            iframe = document.createElement("iframe");
            iframe.id = "printFrame";
            iframe.style.display = "none";
            document.body.appendChild(iframe);
        }

        iframe.src = url;

        iframe.onload = function () {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        };
    });
});
