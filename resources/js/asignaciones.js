$(document).ready(function () {
    const modalAsignacion = new bootstrap.Modal(
        document.getElementById("modalAsignacion"),
    );
    const tabla = $("#tablaAsignaciones").DataTable({
        ajax: route("asignaciones.datatable"),
        columns: [
            { data: "horario"},
            { data: "primer"},
            { data: "segundo"},
            { data: "vehiculo"},
            {
                data: "acciones",
                title: "Acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
        responsive: false,
        dom: "rtip",
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
         drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#btnNuevo").click(function () {
        $("#formAsignacion")[0].reset();
        $("#method").val("POST");
        $("#asignacion_id").val("");
        $("#segundo_conductor_id").prop("disabled", true);
        $("#modalTitulo").text("Nueva Asignación");
        modalAsignacion.show();
    });

    $("#otroConductorCheck").change(function () {
        $("#segundo_conductor_id").prop("disabled", !this.checked);
    });

    $("#formAsignacion").submit(function (e) {
        e.preventDefault();
        let id = $("#asignacion_id").val();
        let url = id
            ? route("asignaciones.update", id)
            : route("asignaciones.store");
        let method = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function (res) {
                modalAsignacion.hide();
                tabla.ajax.reload();
                Swal.fire("Éxito", res.message, "success");
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "Ocurrió un error",
                    "error",
                );
            },
        });
    });

    tabla.on("click", ".editar", function () {
        let id = $(this).data("id");
        $.get(route("asignaciones.show", id), function (a) {
            $("#asignacion_id").val(a.id);
            $("#horario_id").val(a.horario_id);
            $("#primer_conductor_id").val(a.primer_conductor_id);

            if (a.segundo_conductor_id) {
                $("#otroConductorCheck").prop("checked", true);
                $("#segundo_conductor_id")
                    .prop("disabled", false)
                    .val(a.segundo_conductor_id);
            } else {
                $("#otroConductorCheck").prop("checked", false);
                $("#segundo_conductor_id").prop("disabled", true).val("");
            }

            $("#vehiculo").val(a.vehiculo);
            $("#method").val("PUT");
            $("#modalTitulo").text("Editar Asignación");
            modalAsignacion.show();
        });
    });

    tabla.on("click", ".eliminar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar asignación?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("asignaciones.destroy", id),
                    type: "DELETE",
                    success: function (res) {
                        tabla.ajax.reload();
                        Swal.fire("Eliminado", res.message, "success");
                    },
                });
            }
        });
    });

    $("#primer_conductor_id").change(function () {
        let seleccionado = $(this).val();

        $("#segundo_conductor_id option").each(function () {
            if ($(this).val() == seleccionado && seleccionado !== "") {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        if ($("#segundo_conductor_id").val() == seleccionado) {
            $("#segundo_conductor_id").val("");
        }
    });

    const filtros = [
        "filtro_origen",
        "filtro_destino",
        "filtro_tipo_viaje",
        "filtro_tipo_vehiculo",
        "filtro_fecha",
    ];

    filtros.forEach((id) => {
        document.getElementById(id)?.addEventListener("change", cargarHorarios);
    });

    function cargarHorarios() {
        const params = {
            origen: $("#filtro_origen").val(),
            destino: $("#filtro_destino").val(),
            tipo_viaje: $("#filtro_tipo_viaje").val(),
            tipo_vehiculo: $("#filtro_tipo_vehiculo").val(),
            fecha: $("#filtro_fecha").val(),
        };

        fetch(route("horarios.filtrar", params))
            .then((res) => res.json())
            .then((horarios) => {
                const select = $("#horario_id");
                select.html('<option value="">Seleccione un horario</option>');

                horarios.forEach((h) => {
                    const text =
                        `${h.tipo_vehiculo.descripcion} ` +
                        `${h.tipo_viaje.descripcion} | ` +
                        `${h.fecha_salida} | ` + // antes era fecha_salida_formateada
                        `${h.hora_salida} | ` + // antes hora_embarque
                        `${h.punto_origen.nombre_comercial} → ` +
                        `${h.punto_destino.nombre_comercial}`;

                    select.append(
                        `<option value="${h.id}" data-tipo="${h.tipo_vehiculo.id}">
                    ${text}
                </option>`,
                    );
                });
            })
            .catch((err) => console.error(err));
    }

    $("#horario_id").change(function () {
        let tipo = $("#horario_id option:selected").data("tipo");

        $("#vehiculo option").each(function () {
            let tipoVeh = $(this).data("tipo");

            if (!tipo || $(this).val() === "") {
                $(this).show();
            } else if (tipoVeh == tipo) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        if ($("#vehiculo option:selected").is(":hidden")) {
            $("#vehiculo").val("");
        }
    });
});
