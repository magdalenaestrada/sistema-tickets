$(function () {
    if (!$("#tablaEncomiendas").length || !$("#salidaAsignar").length) return;

    const csrf = $('meta[name="csrf-token"]').attr("content");
    let timer = null;
    let puntosPorSalida = {};

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
        valueField: "value",
        labelField: "text",
        searchField: "text",
    });

    
    const tabla = $("#tablaEncomiendas").DataTable({
        processing: true,
        serverSide: true,
        dom: "rtip", 
        ajax: {
            url: route("encomiendas.datatable-no-asignadas"),
            data: function (d) {
                d.documento = $("#filtroDocumento").val();
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
        }, 300);
    }

    function ocultarPuntosRuta() {
        $("#boxPuntosRuta").hide();
        $("#boxPuntosRutaVacio").show();
        $("#listaPuntosRuta").empty();
    }

    function mostrarPuntosRuta(salidaId) {
        const puntos = puntosPorSalida[salidaId] || [];
        const $lista = $("#listaPuntosRuta").empty();

        if (puntos.length === 0) {
            $lista.append('<li class="text-muted">Sin puntos registrados</li>');
        } else {
            puntos.forEach((p) => $lista.append(`<li>${p}</li>`));
        }

        $("#boxPuntosRutaVacio").hide();
        $("#boxPuntosRuta").show();
    }

    // ======================================================
    // ASIGNAR ENCOMIENDA: SOLO depende de la fecha de salida.
    // Ya NO depende de filtroOrigen / filtroDestino.
    // ======================================================
    function cargarSalidasDisponibles() {
        const fechaSalida = $("#filtroFechaSalida").val();

        tsSalida.clear();
        tsSalida.clearOptions();
        puntosPorSalida = {};
        ocultarPuntosRuta();

        if (!fechaSalida) {
            tsSalida.addOption({ value: "", text: "Seleccione una fecha" });
            tsSalida.refreshOptions(false);
            return;
        }

        $.get(route("encomiendas.salidas-disponibles"), {
            fecha_salida: fechaSalida,
        }).done(function (resp) {
            if (!resp.length) {
                tsSalida.addOption({ value: "", text: "No hay horarios programados para esa fecha" });
                tsSalida.refreshOptions(false);
                return;
            }

            resp.forEach((s) => {
                puntosPorSalida[s.value] = s.puntos || [];
            });

            tsSalida.addOptions(resp);
            tsSalida.refreshOptions(false);
        });
    }

    tsSalida.on("change", function (value) {
        if (!value) {
            ocultarPuntosRuta();
            return;
        }
        mostrarPuntosRuta(value);
    });

    // ======================================================
    // EVENTOS
    // ======================================================
    $("#filtroDocumento").on("input", function () {
        const valor = $(this).val().trim();
        if (valor.length > 0 && valor.length < 3) return;
        debounceReload();
    });

    $("#filtroOrigen").on("change", debounceReload);
    $("#filtroDestino").on("change", debounceReload);

    $("#filtroFechaSalida").on("change", cargarSalidasDisponibles);

    $("#btnLimpiar").on("click", function () {
        // Limpia SOLO el bloque "Buscar encomienda" (filtros de tabla).
        $("#filtroDocumento").val("");
        tsOrigen.clear();
        tsDestino.clear();
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