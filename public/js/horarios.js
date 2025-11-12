$(document).ready(function () {
    // 🔹 Modal de crear/editar/ver horario
    const modalHorario = new bootstrap.Modal($("#modalHorario")[0]);
    const formHorario = $("#formHorario");
    // 🔹 DataTable de horarios
    const tabla = $("#tablaHorarios").DataTable({
        ajax: "/horarios/datatable",
        columns: [
            { data: "id" },
            { data: "tipo_viaje" },
            { data: "origen" },
            { data: "destino" },
            { data: "tipo_vehiculo" },
            { data: "costo_pasaje" },
            { data: "hora_embarque" },
            { data: "fecha_salida" },
            {
                data: null,
                render: function (data, type, row) {
                    let dias = [];
                    if (row.lunes) dias.push("L");
                    if (row.martes) dias.push("M");
                    if (row.miercoles) dias.push("X");
                    if (row.jueves) dias.push("J");
                    if (row.viernes) dias.push("V");
                    if (row.sabado) dias.push("S");
                    if (row.domingo) dias.push("D");
                    return dias.join(", ");
                },
            },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    // 🔹 Botón nuevo horario
    $("#btnNuevoHorario").click(function () {
        formHorario[0].reset();
        $("#horario_id").val("");
        $("#modalTitulo").text("Registrar Horario");
        $("#formHorario input, #formHorario select").prop("disabled", false);
        modalHorario.show();
    });

    // 🔹 Guardar/Actualizar horario
    formHorario.submit(function (e) {
        e.preventDefault();

        const formData = $(this).serializeArray();
        $("#formHorario input[type=checkbox]").each(function () {
            let name = $(this).attr("name");
            formData.push({ name: name, value: this.checked ? 1 : 0 });
        });

        let id = $("#horario_id").val();
        let url = id ? `/horarios/${id}` : $(this).attr("action");
        let method = "POST";
        if (id) formData.push({ name: "_method", value: "PUT" });

        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    tabla.ajax.reload(null, false);
                    modalHorario.hide();
                    Swal.fire(
                        "Éxito",
                        "Horario guardado correctamente",
                        "success"
                    );
                } else {
                    Swal.fire(
                        "Error",
                        res.message || "Ocurrió un error",
                        "error"
                    );
                }
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors;
                let msg = errors
                    ? Object.values(errors).flat().join("<br>")
                    : "Error al procesar la solicitud";
                Swal.fire("Error", msg, "error");
            },
        });
    });

    // 🔹 Editar horario
    $("#tablaHorarios").on("click", ".editar", function () {
        let id = $(this).data("id");
        $.get(`/horarios/${id}`, function (data) {
            $("#horario_id").val(data.id);
            $("#tipo_viaje_id").val(data.tipo_viaje_id).prop("disabled", false);
            $("#tipo_vehiculo_id")
                .val(data.tipo_vehiculo_id)
                .prop("disabled", false);
            $("#punto_origen_id")
                .val(data.punto_origen_id)
                .prop("disabled", false);
            $("#punto_destino_id")
                .val(data.punto_destino_id)
                .prop("disabled", false);
            $("#costo_pasaje").val(data.costo_pasaje).prop("disabled", false);
            $("#hora_embarque").val(data.hora_embarque).prop("disabled", false);
            $("#fecha_salida").val(data.fecha_salida).prop("disabled", false);
            $("#lunes").prop("checked", data.lunes).prop("disabled", false);
            $("#martes").prop("checked", data.martes).prop("disabled", false);
            $("#miercoles")
                .prop("checked", data.miercoles)
                .prop("disabled", false);
            $("#jueves").prop("checked", data.jueves).prop("disabled", false);
            $("#viernes").prop("checked", data.viernes).prop("disabled", false);
            $("#sabado").prop("checked", data.sabado).prop("disabled", false);
            $("#domingo").prop("checked", data.domingo).prop("disabled", false);

            $("#modalTitulo").text("Editar Horario");
            modalHorario.show();
        });
    });

    // 🔹 Ver horario
    $("#tablaHorarios").on("click", ".ver", function () {
        let id = $(this).data("id");
        $.get(`/horarios/${id}`, function (data) {
            $("#horario_id").val(data.id);
            $("#tipo_viaje_id").val(data.tipo_viaje_id).prop("disabled", true);
            $("#tipo_vehiculo_id")
                .val(data.tipo_vehiculo_id)
                .prop("disabled", true);
            $("#punto_origen_id")
                .val(data.punto_origen_id)
                .prop("disabled", true);
            $("#punto_destino_id")
                .val(data.punto_destino_id)
                .prop("disabled", true);
            $("#costo_pasaje").val(data.costo_pasaje).prop("disabled", true);
            $("#hora_embarque").val(data.hora_embarque).prop("disabled", true);
            $("#fecha_salida").val(data.fecha_salida).prop("disabled", true);
            $("#lunes").prop("checked", data.lunes).prop("disabled", true);
            $("#martes").prop("checked", data.martes).prop("disabled", true);
            $("#miercoles")
                .prop("checked", data.miercoles)
                .prop("disabled", true);
            $("#jueves").prop("checked", data.jueves).prop("disabled", true);
            $("#viernes").prop("checked", data.viernes).prop("disabled", true);
            $("#sabado").prop("checked", data.sabado).prop("disabled", true);
            $("#domingo").prop("checked", data.domingo).prop("disabled", true);

            $("#modalTitulo").text("Ver Horario");
            modalHorario.show();
        });
    });

    // 🔹 Eliminar horario
    $("#tablaHorarios").on("click", ".eliminar", function () {
        let id = $(this).data("id");
        Swal.fire({
            title: "¿Está seguro?",
            text: "No podrá revertir esto",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/horarios/${id}`,
                    type: "POST",
                    data: { _method: "DELETE" },
                    success: function (res) {
                        if (res.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire("Eliminado", res.message, "success");
                        } else {
                            Swal.fire("Error", res.message, "error");
                        }
                    },
                });
            }
        });
    });

    // 🔹 Ocultar opción destino igual al origen
    $("#punto_origen_id").change(function () {
        let origen = $(this).val();
        $("#punto_destino_id option").each(function () {
            if ($(this).val() === origen && origen !== "") {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        if ($("#punto_destino_id").val() === origen)
            $("#punto_destino_id").val("");
    });
});
