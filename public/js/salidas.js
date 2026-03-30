let tablaSalidas;
let horariosSalida = window.HORARIOS_SALIDA || [];

$(document).ready(function () {
    tablaSalidas = $("#tablaSalidas").DataTable({
        ajax: {
            url: route("salidas.datatable"),
        },
        columns: [
            { data: "id" },
            { data: "ruta" },
            { data: "fecha_formateada" },
            { data: "hora_salida" },
            { data: "hora_llegada" },
            { data: "estado_badge" },
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

function opcionesHorarios(selected = "") {
    let html = `<option value="">Seleccione horario</option>`;

    horariosSalida.forEach((h) => {
        html += `<option value="${h.id}" ${String(selected) === String(h.id) ? "selected" : ""}>${h.nombre}</option>`;
    });

    return html;
}

function opcionesEstados(selected = "programado") {
    let estados = [
        { value: "programado", label: "Programado" },
        { value: "en_ruta", label: "En ruta" },
        { value: "finalizado", label: "Finalizado" },
        { value: "cancelado", label: "Cancelado" },
    ];

    let html = `<option value="">Seleccione estado</option>`;

    estados.forEach((e) => {
        html += `<option value="${e.value}" ${String(selected) === String(e.value) ? "selected" : ""}>${e.label}</option>`;
    });

    return html;
}

window.modoCrearSalida = function () {
    let html = `
        <div class="mb-2">
            <label class="form-label">Horario</label>
            <select id="horario_id" class="form-select">
                ${opcionesHorarios()}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha</label>
            <input type="date" id="fecha_salida" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Estado</label>
            <select id="estado" class="form-select">
                ${opcionesEstados()}
            </select>
        </div>

        <button class="btn btn-primary w-100 mt-2" onclick="guardarSalida()">
            Guardar salida
        </button>
    `;

    $("#tituloPanelSalida").text("Crear salida");
    $("#panelSalidaContenido").html(html);
    lucide.createIcons();
};

window.modoGenerarSalidas = function () {
    let html = `
        <h6>Generar Salidas</h6>

        <div class="mb-2">
            <label class="form-label">Horario</label>
            <select id="horario_id_generar" class="form-select">
                ${opcionesHorarios()}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha inicio</label>
            <input type="date" id="fecha_inicio" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha fin</label>
            <input type="date" id="fecha_fin" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Días</label>

            <div class="d-flex flex-column gap-1">
                <label><input type="checkbox" class="dia" value="1"> Lunes</label>
                <label><input type="checkbox" class="dia" value="2"> Martes</label>
                <label><input type="checkbox" class="dia" value="3"> Miércoles</label>
                <label><input type="checkbox" class="dia" value="4"> Jueves</label>
                <label><input type="checkbox" class="dia" value="5"> Viernes</label>
                <label><input type="checkbox" class="dia" value="6"> Sábado</label>
                <label><input type="checkbox" class="dia" value="7"> Domingo</label>
            </div>
        </div>

        <button class="btn btn-success w-100 mt-2" onclick="generarSalidas()">
            Generar salidas
        </button>
    `;

    $("#tituloPanelSalida").text("Generar salidas");
    $("#panelSalidaContenido").html(html);
    lucide.createIcons();
};

window.guardarSalida = function () {
    let horario_id = $("#horario_id").val();
    let fecha_salida = $("#fecha_salida").val();
    let estado = $("#estado").val();

    if (!horario_id || !fecha_salida || !estado) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("salidas.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        horario_id: horario_id,
        fecha_salida: fecha_salida,
        estado: estado,
    })
        .done(function () {
            Swal.fire("Guardado", "", "success");
            tablaSalidas.ajax.reload();
            $("#panelSalidaContenido").html(
                '<p class="text-muted">Selecciona una salida</p>',
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

window.generarSalidas = function () {
    let horario_id = $("#horario_id_generar").val();
    let fecha_inicio = $("#fecha_inicio").val();
    let fecha_fin = $("#fecha_fin").val();

    let dias = [];
    $(".dia:checked").each(function () {
        dias.push($(this).val());
    });

    if (!horario_id || !fecha_inicio || !fecha_fin || dias.length === 0) {
        Swal.fire("Error", "Completa todos los campos", "error");
        return;
    }

    Swal.fire({
        title: "Generando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("salidas.generar"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        horario_id: horario_id,
        fecha_inicio: fecha_inicio,
        fecha_fin: fecha_fin,
        dias: dias,
    })
        .done(function (res) {
            Swal.fire(
                "Correcto",
                res.mensaje || "Salidas generadas",
                "success",
            );
            tablaSalidas.ajax.reload();
            $("#panelSalidaContenido").html(
                '<p class="text-muted">Selecciona una salida</p>',
            );
        })
        .fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudieron generar",
                "error",
            );
        });
};

function verSalida(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let puntos = "";

        if (salida.ruta?.puntos?.length) {
            puntos = `
                <ul class="list-group mt-2">
                    ${salida.ruta.puntos
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
            <h6>${salida.ruta?.nombre ?? "Sin ruta"}</h6>

            <div class="mb-2"><strong>Fecha:</strong> ${salida.fecha_formateada ?? "-"}</div>
            <div class="mb-2"><strong>Hora salida:</strong> ${salida.hora_salida ?? "-"}</div>
            <div class="mb-2"><strong>Hora llegada:</strong> ${salida.hora_llegada ?? "-"}</div>
            <div class="mb-2"><strong>Tipo viaje:</strong> ${salida.tipo_viaje ?? "-"}</div>
            <div class="mb-2"><strong>Tipo vehículo:</strong> ${salida.tipo_vehiculo ?? "-"}</div>
            <div class="mb-2"><strong>Estado:</strong> ${salida.estado ?? "-"}</div>

            <hr>

            <h6>Puntos de la ruta</h6>
            ${puntos}
        `;

        $("#tituloPanelSalida").text("Detalle de salida");
        $("#panelSalidaContenido").html(html);
        lucide.createIcons();
    });
}

function editarSalida(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let html = `
            <h6>Editar Salida</h6>

            <div class="mb-2">
                <label class="form-label">Horario</label>
                <select id="horario_id" class="form-select">
                    ${opcionesHorarios(salida.horario_id)}
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label">Fecha</label>
                <input type="date" id="fecha_salida" class="form-control" value="${salida.fecha_salida}">
            </div>

            <div class="mb-2">
                <label class="form-label">Estado</label>
                <select id="estado" class="form-select">
                    ${opcionesEstados(salida.estado)}
                </select>
            </div>

            <button class="btn btn-success w-100 mt-2" onclick="guardarEdicionSalida(${salida.id})">
                Guardar cambios
            </button>
        `;

        $("#tituloPanelSalida").text("Editar salida");
        $("#panelSalidaContenido").html(html);
        lucide.createIcons();
    });
}

window.guardarEdicionSalida = function (id) {
    let horario_id = $("#horario_id").val();
    let fecha_salida = $("#fecha_salida").val();
    let estado = $("#estado").val();

    if (!horario_id || !fecha_salida || !estado) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    Swal.fire({
        title: "Actualizando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: route("salidas.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
            _method: "PUT",
            horario_id: horario_id,
            fecha_salida: fecha_salida,
            estado: estado,
        },
        success: function () {
            Swal.fire("Actualizado", "", "success");
            tablaSalidas.ajax.reload();
            $("#panelSalidaContenido").html(
                '<p class="text-muted">Selecciona una salida</p>',
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

function eliminarSalida(id) {
    Swal.fire({
        title: "¿Eliminar salida?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.destroy", { id: id }),
            method: "POST",
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE",
            },
            success: function () {
                Swal.fire("Eliminado", "", "success");
                tablaSalidas.ajax.reload();
                $("#panelSalidaContenido").html(
                    '<p class="text-muted">Selecciona una salida</p>',
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
    verSalida(id);
});

$(document).on("click", ".editar", function () {
    let id = $(this).data("id");
    editarSalida(id);
});

$(document).on("click", ".eliminar", function () {
    let id = $(this).data("id");
    eliminarSalida(id);
});
