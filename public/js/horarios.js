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
            { data: "costo_base" },
            { data: "hora_salida" },
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
        limpiarPuntos();
        toggleContenedorPuntos();
        modalHorario.show();
    });
    const fechaSalida = document.getElementById("fecha_salida");
    const repetirHasta = document.getElementById("repetir_hasta");

    fechaSalida.addEventListener("change", function () {
        repetirHasta.min = this.value;

        if (repetirHasta.value && repetirHasta.value < this.value) {
            repetirHasta.value = "";
        }
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
            $("#costo_pasaje").val(data.costo_base).prop("disabled", false);
            $("#hora_salida").val(data.hora_salida).prop("disabled", false);
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

            toggleContenedorPuntos();

            limpiarPuntos();

            if (data.tramos && data.tramos.length > 0) {
                let origenId = data.punto_origen_id;
                let origenText = $("#punto_origen_id option:selected").text();

                data.tramos.forEach((t) => {
                    const destinoText =
                        t.destino?.sucursal?.nombre_comercial || "Destino";
                    const horaLlegada = t.hora_llegada;

                    puntosData.push({
                        origen_id: origenId,
                        destino_id: t.punto_destino_id,
                        destino_text: destinoText,
                        costo: parseFloat(t.costo_tramo),
                        duracion: parseInt(t.duracion_minutos),
                        index: puntoIndex,
                    });

                    $("#tablaPuntos tbody").append(`
<tr data-index="${puntoIndex}">
    <td>${origenText}</td>
    <td>${destinoText}</td>
    <td>S/ ${parseFloat(t.costo_tramo).toFixed(2)}</td>
    <td>${t.duracion_minutos} min</td>
    <td>${horaLlegada}</td>
    <td>
        <button type="button" class="btn btn-danger btn-sm eliminarPunto">
            Eliminar
        </button>
    </td>
</tr>
`);

                    $("#inputsPuntos").append(`
<input type="hidden" name="puntos[${puntoIndex}][sucursal_id]" value="${t.punto_destino_id}">
<input type="hidden" name="puntos[${puntoIndex}][costo]" value="${t.costo_tramo}">
<input type="hidden" name="puntos[${puntoIndex}][duracion]" value="${t.duracion_minutos}">
`);

                    puntoIndex++;
                    origenId = t.punto_destino_id;
                    origenText = destinoText;
                });

                actualizarCostoFinal();
            }

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
            $("#hora_salida").val(data.hora_salida).prop("disabled", true);
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
        toggleContenedorPuntos();
    });

    function toggleContenedorPuntos() {
        const tipoViaje = $("#tipo_viaje_id").val();
        if (tipoViaje == 2) {
            $("#contenedorPuntos").removeClass("d-none");

            $(".contenedor_destino").addClass("d-none");
            $(".contenedor_costo_pasaje").addClass("d-none");

            $("#punto_destino_id").prop("required", false).val("");
            $("#costo_pasaje").prop("required", false);
        } else {
            $("#contenedorPuntos").addClass("d-none");

            $(".contenedor_destino").removeClass("d-none");
            $(".contenedor_costo_pasaje").removeClass("d-none");

            $("#punto_destino_id").prop("required", true);
            $("#costo_pasaje").prop("required", true);

            limpiarPuntos();
        }
    }

    $("#tipo_viaje_id").change(toggleContenedorPuntos);

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

    let puntosData = [];
    let puntoIndex = 0;

    $("#btnAgregarPunto").click(function () {
        let origenId;
        let origenText;

        if (puntosData.length === 0) {
            origenId = $("#punto_origen_id").val();
            origenText = $("#punto_origen_id option:selected").text();
        } else {
            const ultimo = puntosData[puntosData.length - 1];
            origenId = ultimo.destino_id;
            origenText = $(
                "#punto_destino option[value='" + origenId + "']",
            ).text();
        }

        const destinoId = $("#punto_destino").val();
        const destinoText = $("#punto_destino option:selected").text();
        const costo = $("#costo_tramo").val();
        const duracion = $("#duracion_tramo").val();

        if (!destinoId || !costo || !duracion) {
            Swal.fire("Error", "Complete todos los campos del tramo", "error");
            return;
        }

        if (destinoId === origenId) {
            Swal.fire(
                "Error",
                "El destino no puede ser igual al origen",
                "error",
            );
            return;
        }

        if (puntosData.some((p) => p.destino_id == destinoId)) {
            Swal.fire("Error", "Este destino ya fue agregado", "error");
            return;
        }

        const origenHorario = $("#punto_origen_id").val(); // origen principal del horario

        puntosData.push({
            origen_id: origenId,
            destino_id: destinoId,
            costo: parseFloat(costo),
            duracion: parseInt(duracion),
            index: puntoIndex,
        });

        $("#tablaPuntos tbody").append(`
<tr data-index="${puntoIndex}">
    <td>${origenText}</td>
    <td>${destinoText}</td>
    <td>S/ ${parseFloat(costo).toFixed(2)}</td>
    <td>
        <button type="button" class="btn btn-danger btn-sm eliminarPunto">
            Eliminar
        </button>
    </td>
</tr>
`);

        $("#inputsPuntos").append(`
<input type="hidden" name="puntos[${puntoIndex}][sucursal_id]" value="${destinoId}">
<input type="hidden" name="puntos[${puntoIndex}][costo]" value="${costo}">
<input type="hidden" name="puntos[${puntoIndex}][duracion]" value="${duracion}">
`);

        puntoIndex++;
        $("#punto_destino").val("");
        $("#costo_tramo").val("");
        $("#duracion_tramo").val("");

        actualizarCostoFinal();
    });

    function limpiarPuntos() {
        puntosData = [];
        puntoIndex = 0;
        $("#tablaPuntos tbody").empty();
        $("#inputsPuntos").empty();
    }

    $("#punto_origen_id").change(function () {
        const texto = $("#punto_origen_id option:selected").text();
        $("#origen_nombre").val(texto);

        limpiarPuntos();
    });

    function actualizarCostoFinal() {
        if (puntosData.length === 0) {
            $("#costo_pasaje").val("");
            return;
        }
        const ultimo = puntosData[puntosData.length - 1];
        $("#costo_pasaje").val(ultimo.costo.toFixed(2));
    }
});
