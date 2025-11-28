$(function () {
    // Inicializar DataTable
    let tabla = $("#tablaEncomiendas").DataTable({
        ajax: "/encomiendas/datatable/no-asignadas",
        columns: [
            { data: "checkbox", orderable: false, searchable: false },
            { data: "id" },
            { data: "emisor" },
            { data: "dni_emisor" },
            { data: "receptor" },
            { data: "origen" },
            { data: "destino" },
            { data: "total" },
            { data: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        order: [[1, "desc"]],
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
            actualizarContador();
        },
    });

    // Seleccionar/Deseleccionar todos
    $("#checkAll").on("change", function () {
        $(".check-encomienda").prop("checked", $(this).prop("checked"));
        actualizarContador();
    });

    // Actualizar contador al seleccionar individual
    $(document).on("change", ".check-encomienda", function () {
        actualizarContador();

        // Actualizar checkAll
        let total = $(".check-encomienda").length;
        let seleccionados = $(".check-encomienda:checked").length;
        $("#checkAll").prop("checked", total === seleccionados && total > 0);
    });

    // Función para actualizar contador
    function actualizarContador() {
        let count = $(".check-encomienda:checked").length;
        $("#contadorSeleccionados").text(
            count + " seleccionada" + (count !== 1 ? "s" : "")
        );
    }

    // Botón Asignar
    $("#btnAsignar").on("click", function () {
        let asignacionId = $("#asignacion_id").val();

        if (!asignacionId) {
            alert("Debe seleccionar un horario/asignación");
            return;
        }

        let encomiendas = [];
        $(".check-encomienda:checked").each(function () {
            encomiendas.push($(this).val());
        });

        if (encomiendas.length === 0) {
            alert("Debe seleccionar al menos una encomienda");
            return;
        }

        if (
            !confirm(
                `¿Asignar ${encomiendas.length} encomienda(s) a esta asignación?`
            )
        ) {
            return;
        }

        $.ajax({
            url: "/asignaciones-encomiendas/store",
            method: "POST",
            data: {
                _token: csrf_token,
                asignacion_id: asignacionId,
                encomiendas: encomiendas,
            },
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    tabla.ajax.reload();
                    $("#checkAll").prop("checked", false);
                    $("#asignacion_id").val("");
                }
            },
            error: function (err) {
                alert(
                    err.responseJSON?.message || "Error al asignar encomiendas"
                );
            },
        });
    });

    // Filtros
    $("#filtroDNI").on("keyup", function () {
        tabla.column(3).search(this.value).draw();
    });

    $("#filtroOrigen").on("change", function () {
        tabla.column(5).search(this.value).draw();
    });

    $("#filtroDestino").on("change", function () {
        tabla.column(6).search(this.value).draw();
    });

    $("#btnNueva").click(() => {
        window.location.href = "/encomiendas/crear-encomienda";
    });

    $(document).on("click", ".imprimir", function () {
        let id = $(this).data("id");
        let url = "/encomiendas/ticket/" + id;
        let ventana = window.open(url, "_blank", "width=420,height=650");

        let timer = setInterval(function () {
            if (ventana.document.readyState === "complete") {
                ventana.print();
                clearInterval(timer);
            }
        }, 200);
    });

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id");
        window.location.href = `/encomiendas/editar/${id}`;
    });

    $(document).on("click", ".anular", function () {
        if (!confirm("¿Seguro de anular esta encomienda?")) return;

        let id = $(this).data("id");
        $.post(
            `/encomiendas/anular/${id}`,
            { _token: csrf_token },
            function (res) {
                if (res.success) {
                    tabla.ajax.reload();
                }
            }
        ).fail(function () {
            alert("Error al anular la encomienda");
        });
    });
});
