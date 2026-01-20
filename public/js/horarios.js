$(document).ready(function () {
    const modalHorario = new bootstrap.Modal($("#modalHorario")[0]);
    const formHorario = $("#formHorario");
    const tabla = $("#tablaHorarios").DataTable({
        ajax: route("horarios.datatable"),
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
        dom: "rtip",
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
        let url = id
            ? route("horarios.actualizar", id)
            : $(this).attr("action");
        let method = id ? "PUT" : "POST";
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
                        "success",
                    );
                } else {
                    Swal.fire(
                        "Error",
                        res.message || "Ocurrió un error",
                        "error",
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
        $.get(route("horarios.mostrar", id), function (data) {
            $("#horario_id").val(data.id);
            $("#tipo_viaje_id").val(data.tipo_viaje_id).prop("disabled", false);
            $("#tipo_horario_id")
                .val(data.tipo_horario_id)
                .prop("disabled", false);
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
        $.get(route("horarios.mostrar", id), function (data) {
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
                    url: route("horarios.eliminar", id),
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
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

    // MODAL DE PUNTOS Y TRAMOS
    const modalPuntos = new bootstrap.Modal($("#modalPuntos")[0]);
    let horarioIdActivo = null;
    let origenIdActivo = null;
    let puntoEditActivo = null;
    let puntosData = []; // Guardar los datos de los puntos

    $("#tablaHorarios").on("click", ".ver-puntos", function () {
        horarioIdActivo = $(this).data("id");
        origenIdActivo = $(this).data("origen-id");
        puntoEditActivo = null;

        $("#formPunto")[0].reset();
        $("#origen_nombre").val($(this).data("origen"));

        cargarPuntos(horarioIdActivo);
        modalPuntos.show();
    });

    function cargarPuntos(horarioId) {
        $.get(
            route("horarios.mostrar", horarioId) + "/puntos",
            function (puntos) {
                puntosData = puntos; // Guardar los datos completos
                const tbody = $("#tablaPuntos tbody");
                tbody.empty();

                if (puntos.length === 0) return;

                // INVERTIR EL ORDEN: mostrar del último al primero
                const puntosInvertidos = [...puntos].reverse();

                puntosInvertidos.forEach((punto, index) => {
                    const origen =
                        index === 0
                            ? `<td rowspan="${puntos.length}" class="fw-bold text-center align-middle">
                    ${punto.origen.nombre_comercial}
               </td>`
                            : "";

                    tbody.append(`
            <tr>
                ${origen}
                <td>${punto.destino.nombre_comercial}</td>
                <td>S/ ${parseFloat(punto.costo_acumulado).toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm eliminarPunto"
                            data-id="${punto.id}">
                        Eliminar
                    </button>
                </td>
            </tr>
        `);
                });

                lucide.createIcons();
            },
        );
    }

    // Función para contar cuántas veces aparece un destino POR ID
    function contarDestinoEnTabla(destinoId) {
        let count = 0;
        puntosData.forEach(punto => {
            if (punto.destino_id == destinoId) {
                count++;
            }
        });
        return count;
    }

    // Función para obtener el PRIMER costo agregado (el más lejano, que es el mayor)
    function obtenerPrimerCosto() {
        if (puntosData.length === 0) return null;
        // El primer punto tiene el costo más alto (más lejano)
        return parseFloat(puntosData[0].costo_acumulado);
    }

    $("#formPunto").submit(function (e) {
        e.preventDefault();

        const destinoId = $("#destino_id").val();
        const costoNuevo = parseFloat($("#costo_acumulado").val());

        // VALIDACIÓN 1: Origen no puede ser igual al destino
        if (destinoId == origenIdActivo) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "El destino no puede ser igual al origen",
            });
            return;
        }

        // VALIDACIÓN 2: No permitir agregar un punto más de 2 veces
        if (!puntoEditActivo) {
            const cantidadExistente = contarDestinoEnTabla(destinoId);
            if (cantidadExistente >= 2) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Este destino ya ha sido agregado 2 veces. No puede agregarse más.",
                });
                return;
            }
        }

        // VALIDACIÓN 3: El costo debe ser menor o igual al primer punto (el más lejano)
        const primerCosto = obtenerPrimerCosto();
        if (primerCosto !== null && costoNuevo > primerCosto) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: `El costo debe ser menor o igual a S/ ${primerCosto.toFixed(2)} (último punto más lejano)`,
            });
            return;
        }

        const formData = $(this).serialize();
        let url = puntoEditActivo
            ? route("horarios.mostrar", horarioIdActivo) +
              "/puntos/" +
              puntoEditActivo
            : route("horarios.mostrar", horarioIdActivo) + "/puntos";
        let method = puntoEditActivo ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    cargarPuntos(horarioIdActivo);
                    $("#formPunto")[0].reset();
                    puntoEditActivo = null;
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: "Punto guardado correctamente",
                    });
                }
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors;
                let msg = errors
                    ? Object.values(errors).flat().join("<br>")
                    : "Error al procesar la solicitud";
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: msg,
                });
            },
        });
    });

    $("#tablaPuntos").on("click", ".editarPunto", function () {
        puntoEditActivo = $(this).data("id");
        $.get(
            route("horarios.mostrar", horarioIdActivo) +
                "/puntos/" +
                puntoEditActivo,
            function (p) {
                $("#destino_id").val(p.destino_id);
                $("#costo_acumulado").val(p.costo_acumulado);
            },
        );
    });

    // BOTÓN ELIMINAR CORREGIDO
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
                    url:
                        route("horarios.mostrar", horarioIdActivo) +
                        "/puntos/" +
                        puntoId,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    success: function (res) {
                        if (res.success) {
                            cargarPuntos(horarioIdActivo);
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "Punto eliminado correctamente",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: res.message || "Error al eliminar",
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el punto",
                        });
                    },
                });
            }
        });
    });
});