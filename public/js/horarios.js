$(document).ready(function () {
    const modalHorario = new bootstrap.Modal($("#modalHorario")[0]);
    const formHorario = $("#formHorario");
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

    $("#btnNuevoHorario").click(function () {
        formHorario[0].reset();
        $("#horario_id").val("");
        $("#modalTitulo").text("Registrar Horario");
        $("#formHorario input, #formHorario select").prop("disabled", false);
        modalHorario.show();
    });

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

    $(document).ready(function () {
        const modalPuntos = new bootstrap.Modal($("#modalPuntos")[0]);
        let horarioIdActivo = null;
        let puntoEditActivo = null;

        $("#tablaHorarios").on("click", ".ver-puntos", function () {
            horarioIdActivo = $(this).data("id");
            puntoEditActivo = null;
            $("#formPunto")[0].reset();
            cargarPuntos(horarioIdActivo);
            modalPuntos.show();
        });

        function cargarPuntos(horarioId) {
            $("#tablaPuntos tbody").empty();
            $("#tablaTramos tbody").empty();

            $.get(`/horarios/${horarioId}/puntos`, function (puntos) {
                puntos.sort((a, b) => b.orden - a.orden);

                // Puntos
                puntos.forEach((p) => {
                    $("#tablaPuntos tbody").append(`
        <tr>
            <td>${p.origen ? p.origen.nombre_comercial : ""}</td>
            <td>${p.destino ? p.destino.nombre_comercial : ""}</td>
<td>${parseFloat(p.costo_acumulado).toFixed(2)}</td>
            <td>
                <button class="btn btn-warning btn-xs editarPunto" data-id="${
                    p.id
                }">Editar</button>
                <button class="btn btn-danger btn-xs eliminarPunto" data-id="${
                    p.id
                }">Eliminar</button>
            </td>
        </tr>
    `);
                });

                for (let i = 0; i < puntos.length; i++) {
                    let origen =
                        i === 0
                            ? puntos[i].origen.nombre_comercial
                            : puntos[i - 1].destino.nombre_comercial;
                    let destino = puntos[i].destino.nombre_comercial;
                    let costoTramo =
                        i === 0
                            ? puntos[i].costo_acumulado
                            : puntos[i].costo_acumulado -
                              puntos[i - 1].costo_acumulado;

                    $("#tablaTramos tbody").append(`
        <tr>
            <td>${origen}</td>
            <td>${destino}</td>
            <td>${costoTramo.toFixed(2)}</td>
        </tr>
    `);
                }

                // Excluir origen del select
                const origenNombre =
                    puntos.length > 0 ? puntos[0].origen.nombre_comercial : "";
                $("#destino_id option").each(function () {
                    if ($(this).text() === origenNombre) $(this).hide();
                    else $(this).show();
                });
            });
        }

        // Guardar o editar punto
        $("#formPunto").submit(function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            let url = puntoEditActivo
                ? `/horarios/${horarioIdActivo}/puntos/${puntoEditActivo}`
                : `/horarios/${horarioIdActivo}/puntos`;
            let method = puntoEditActivo ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function (res) {
                    if (res.success) {
                        cargarPuntos(horarioIdActivo);
                        $("#formPunto")[0].reset();
                        puntoEditActivo = null;
                        Swal.fire(
                            "Éxito",
                            "Punto guardado correctamente",
                            "success"
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

        // Editar punto
        $("#tablaPuntos").on("click", ".editarPunto", function () {
            puntoEditActivo = $(this).data("id");
            $.get(
                `/horarios/${horarioIdActivo}/puntos/${puntoEditActivo}`,
                function (p) {
                    $("#destino_id").val(p.destino_id);
                    $("#costo_acumulado").val(p.costo_acumulado);
                }
            );
        });

        // Eliminar punto
        $("#tablaPuntos").on("click", ".eliminarPunto", function () {
            const puntoId = $(this).data("id");
            Swal.fire({
                title: "¿Eliminar punto?",
                text: "No se podrá revertir",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/horarios/${horarioIdActivo}/puntos/${puntoId}`,
                        type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        success: function (res) {
                            if (res.success) {
                                cargarPuntos(horarioIdActivo);
                                Swal.fire(
                                    "Eliminado",
                                    "Punto eliminado correctamente",
                                    "success"
                                );
                            }
                        },
                    });
                }
            });
        });
    });
});
