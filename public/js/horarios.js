let tablaHorarios;
let rutasHorario = window.RUTAS_HORARIO || [];
let tiposViajeHorario = window.TIPOS_VIAJE_HORARIO || [];
let tiposVehiculoHorario = window.TIPOS_VEHICULO_HORARIO || [];

$(document).ready(async function () {
    tablaHorarios = $("#tablaHorarios").DataTable({
        ajax: {
            url: route("horarios.datatable"),
        },
        columns: [
            { data: "id" },
            { data: "ruta" },
            { data: "hora_salida_formateada" },
            { data: "hora_llegada_formateada" },
            { data: "duracion" },
            { data: "acciones" },
        ],
        responsive: true,
        info: false,
        dom: "rtip",
        drawCallback: function () {
            lucide.createIcons();
        },
    });
});

function opcionesRutas(selected = "") {
    let html = `<option value="">Seleccione ruta</option>`;

    rutasHorario.forEach((r) => {
        html += `<option value="${r.id}" ${String(selected) === String(r.id) ? "selected" : ""}>${r.nombre}</option>`;
    });

    return html;
}

function opcionesTiposViaje(selected = "") {
    let html = `<option value="">Seleccione tipo de viaje</option>`;

    tiposViajeHorario.forEach((t) => {
        html += `<option value="${t.id}" ${String(selected) === String(t.id) ? "selected" : ""}>${t.descripcion}</option>`;
    });

    return html;
}

function opcionesTiposVehiculo(selected = "") {
    let html = `<option value="">Seleccione tipo de vehículo</option>`;

    tiposVehiculoHorario.forEach((t) => {
        html += `<option value="${t.id}" ${String(selected) === String(t.id) ? "selected" : ""}>${t.descripcion}</option>`;
    });

    return html;
}

window.modoCrearHorario = function () {
    let html = `

        <div class="mb-2">
            <label class="form-label">Ruta <span style="color: red">*</span></label>
            <select id="ruta_id" class="form-select" required>
                ${opcionesRutas()}
            </select>
        </div>


        <div class="mb-2">
            <label class="form-label">Tipo de vehículo <span style="color: red">*</span></label>
            <select id="tipo_vehiculo_id" class="form-select">
                ${opcionesTiposVehiculo()}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">Hora salida <span style="color: red">*</span></label>
            <input type="time" id="hora_salida" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Costo total S/ </label>
            <input type="number" id="costo_base" class="form-control" min="0" step="0.01">
        </div>

        <button class="btn btn-primary w-100 mt-2" onclick="guardarHorario()">
            Guardar horario
        </button>
    `;

    $("#tituloPanelHorario").text("Crear horario");
    $("#panelHorarioContenido").html(html);
    lucide.createIcons();
};

window.guardarHorario = function () {
    let ruta_id = $("#ruta_id").val();
    let tipo_vehiculo_id = $("#tipo_vehiculo_id").val();
    let hora_salida = $("#hora_salida").val();
    let costo_base = $("#costo_base").val();

    if (
        !ruta_id ||
        !tipo_vehiculo_id ||
        !hora_salida
    ) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("horarios.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        ruta_id: ruta_id,
        tipo_vehiculo_id: tipo_vehiculo_id,
        hora_salida: hora_salida,
        costo_base: costo_base,
    })
        .done(function () {
            Swal.fire("Guardado", "", "success");
            tablaHorarios.ajax.reload();
            $("#panelHorarioContenido").html(
                '<p class="text-muted">Selecciona un horario</p>',
            );
        })
        .fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudo guardar",
                "error",
            );
        });
};

function verHorario(id) {
    $.get(route("horarios.show", { id: id }), function (horario) {
        console.log("HORARIO:", horario);

        let puntos = "";

        if (horario.ruta?.puntos?.length) {
            puntos = `
                <ul class="list-group mt-2">
                    ${horario.ruta.puntos
                        .map(
                            (p) => `
                        <li class="list-group-item d-flex justify-content-between">
                            <span>${p.orden}. ${p.nombre}</span>
                        </li>
                    `,
                        )
                        .join("")}
                </ul>
            `;
        }

        let html = `
            <h6>${horario.ruta?.nombre ?? "Sin ruta"}</h6>

            <div class="mb-2"><strong>Tipo viaje:</strong> ${horario.tipo_viaje ?? "-"}</div>
            <div class="mb-2"><strong>Tipo vehículo:</strong> ${horario.tipo_vehiculo ?? "-"}</div>
            <div class="mb-2"><strong>Hora salida:</strong> ${horario.hora_salida_formateada ?? horario.hora_salida ?? "-"}</div>
            <div class="mb-2"><strong>Hora llegada:</strong> ${horario.hora_llegada ?? "-"}</div>
            <div class="mb-2"><strong>Duración:</strong> ${horario.duracion_total ?? "-"}</div>
            <div class="mb-2"><strong>Costo total:</strong> S/ ${horario.costo_base ?? "0.00"}</div>

            <hr>

            <h6>Puntos de la ruta</h6>
            ${puntos}
        `;

        $("#tituloPanelHorario").text("Detalle de horario");
        $("#panelHorarioContenido").html(html);
        lucide.createIcons();
    }).fail(function (err) {
        console.error("ERROR SHOW:", err);
        Swal.fire("Error", "No se pudo cargar el horario", "error");
    });
}

function editarHorario(id) {
    $.get(route("horarios.show", { id: id }), function (horario) {
        let html = `
            <h6>Editar Horario</h6>

            <div class="mb-2">
                <label class="form-label">Ruta <span
                                style="color: red">*</span></label>
                <select id="ruta_id" class="form-select" required>
                    ${opcionesRutas(horario.ruta_id)}
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label">Tipo de vehículo <span style="color: red">*</span></label>
                <select id="tipo_vehiculo_id" class="form-select">
                    ${opcionesTiposVehiculo(horario.tipo_vehiculo_id)}
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label">Hora salida <span style="color: red">*</span></label>
                <input type="time" id="hora_salida" class="form-control" value="${(horario.hora_salida || "").substring(0, 5)}">
            </div>

            <div class="mb-2">
                <label class="form-label">Costo base S/ </label>
                <input type="number" id="costo_base" class="form-control" min="0" step="0.01" value="${horario.costo_base}">
            </div>

            <button class="btn btn-success w-100 mt-2" onclick="guardarEdicionHorario(${horario.id})">
                Guardar cambios
            </button>
        `;

        $("#tituloPanelHorario").text("Editar horario");
        $("#panelHorarioContenido").html(html);
        lucide.createIcons();
    });
}

window.guardarEdicionHorario = function (id) {
    let ruta_id = $("#ruta_id").val();
    let tipo_vehiculo_id = $("#tipo_vehiculo_id").val();
    let hora_salida = $("#hora_salida").val();
    let costo_base = $("#costo_base").val();

    if (
        !ruta_id ||
        !tipo_vehiculo_id ||
        !hora_salida
    ) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    Swal.fire({
        title: "Actualizando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: route("horarios.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
            _method: "PUT",
            ruta_id: ruta_id,
            tipo_vehiculo_id: tipo_vehiculo_id,
            hora_salida: hora_salida,
            costo_base: costo_base,
        },
        success: function () {
            Swal.fire("Actualizado", "", "success");
            tablaHorarios.ajax.reload();
            $("#panelHorarioContenido").html(
                '<p class="text-muted">Selecciona un horario</p>',
            );
        },
        error: function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudo actualizar",
                "error",
            );
        },
    });
};

function eliminarHorario(id) {
    Swal.fire({
        title: "¿Eliminar horario?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("horarios.destroy", { id: id }),
            method: "POST",
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE",
            },
            success: function () {
                Swal.fire("Eliminado", "", "success");
                tablaHorarios.ajax.reload();
                $("#panelHorarioContenido").html(
                    '<p class="text-muted">Selecciona un horario</p>',
                );
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "No se pudo eliminar",
                    "error",
                );
            },
        });
    });
}

$(document).on("click", ".ver", function () {
    let id = $(this).data("id");
    verHorario(id);
});

$(document).on("click", ".editar", function () {
    let id = $(this).data("id");
    editarHorario(id);
});

$(document).on("click", ".eliminar", function () {
    let id = $(this).data("id");
    eliminarHorario(id);
});
