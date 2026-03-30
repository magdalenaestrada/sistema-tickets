let tablaRutas;
let sucursales = [];

$(document).ready(async function () {
    tablaRutas = $("#tablaRutas").DataTable({
        ajax: {
            url: route("rutas.datatable"),
            data: function (d) {
                d.nombre = $("#filtro_nombre").val();
            },
        },

        columns: [
            { data: "id" },
            { data: "nombre" },
            { data: "puntos" },
            { data: "acciones" },
        ],

        responsive: true,
        info: false,
        dom: "rtip",

        drawCallback: function () {
            lucide.createIcons();
        },
    });

    sucursales = await $.get(route("sucursales.lista"));
    agregarPunto();
    agregarPunto();

    window.modoCrear = function () {
        let html = `
       <form id="formRuta">
    <h6 class="mb-2"><b>NOMBRE DE LA RUTA</b></h6>

    <input type="text" id="nombreNuevaRuta"
        class="form-control mb-3"
        placeholder="Nombre de la ruta" required>

    <div id="contenedorPuntos"></div>

    <button type="button" class="btn btn-sm btn-success mb-2"
        onclick="agregarPunto()">
        Añadir Punto
    </button>

    <hr>

    <h6>Tramos</h6>
    <div id="contenedorTramos"></div>

    <button type="submit" class="btn btn-primary w-100 mt-2">
        Guardar ruta
    </button>
</form>
    `;

        $("#tituloPanel").text("Crear Ruta");
        $("#panelContenido").html(html);
        $("#formRuta").on("submit", function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            guardarRuta();
        });
        setTimeout(() => {
            agregarPunto();
            agregarPunto();
        }, 100);
    };

});

window.guardarRuta = function () {
    if (!validarPuntos()) {
        Swal.fire("Error", "Revisa los puntos", "error");
        return;
    }

    let nombre = $("#nombreNuevaRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos select").each(function () {
        puntos.push($(this).val());
    });

    $("input[name='duracion[]']").each(function () {
        duracion.push($(this).val());
    });

    $("input[name='costo[]']").each(function () {
        costo.push($(this).val());
    });

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("rutas.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        nombre: nombre,
        puntos: puntos,
        duracion: duracion,
        costo: costo,
    })
        .done(function () {
            Swal.fire("Guardado", "", "success");

            tablaRutas.ajax.reload();

            $("#panelContenido").html(
                '<p class="text-muted">Selecciona una ruta</p>',
            );
        })
        .fail(function () {
            Swal.fire("Error", "No se pudo guardar", "error");
        });
};

function obtenerOpcionesDisponibles() {
    let seleccionados = [];

    $("#contenedorPuntos select").each(function () {
        let val = $(this).val();
        if (val) seleccionados.push(val);
    });

    let options = `<option value="">Seleccione sucursal</option>`;

    sucursales.forEach((s) => {
        if (!seleccionados.includes(String(s.id))) {
            options += `<option value="${s.id}">${s.nombre_comercial}</option>`;
        }
    });

    return options;
}

function agregarPunto(valorSeleccionado = null) {
    let options = obtenerOpcionesDisponibles();

    let index = $("#contenedorPuntos .punto").length + 1;

    let html = `
        <div class="punto d-flex align-items-center gap-2 mb-2">

            <span class="badge bg-secondary">${index}</span>

            <select name="puntos[]" class="form-select" required>
                ${options}
            </select>

            <button type="button" class="btn btn-danger btn-sm"
                onclick="eliminarPunto(this)">
                <i data-lucide="trash"></i>
            </button>

        </div>
    `;

    $("#contenedorPuntos").append(html);

    let select = $("#contenedorPuntos .punto:last select");

    if (valorSeleccionado) {
        select.val(valorSeleccionado);
    }

    lucide.createIcons();
    actualizarOpciones();
    generarTramos();
}

function eliminarPunto(btn) {
    let total = $("#contenedorPuntos .punto").length;

    if (total <= 2) {
        Swal.fire("Debe tener minimo 2 puntos", "", "warning");
        return;
    }

    $(btn).closest(".punto").remove();
    reordenarPuntos();
    generarTramos();
}

function reordenarPuntos() {
    $("#contenedorPuntos .punto").each(function (i) {
        $(this)
            .find(".badge")
            .text(i + 1);
    });
}

function validarPuntos() {
    let valores = [];

    let valido = true;

    $("#contenedorPuntos select").each(function () {
        let val = $(this).val();

        if (!val) {
            valido = false;
        }

        if (valores.includes(val)) {
            valido = false;
        }

        valores.push(val);
    });

    return valido;
}

function actualizarOpciones() {
    let seleccionados = [];

    $("#contenedorPuntos select").each(function () {
        let val = $(this).val();

        if (val) {
            seleccionados.push(val);
        }
    });

    $("#contenedorPuntos select").each(function () {
        let current = $(this).val();

        $(this)
            .find("option")
            .each(function () {
                let val = $(this).val();

                if (val === "") return;

                if (seleccionados.includes(val) && val !== current) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
    });
}

$(document).on("change", "#contenedorPuntos select", function () {
    actualizarOpciones();
    generarTramos();
});

function verRuta(id) {
    $.get(route("rutas.show", id), function (ruta) {
        let total = ruta.puntos.length;

        let html = `
            <h6>${ruta.nombre}</h6>

            <p class="text-muted mb-2">
                Total de puntos: <strong>${total}</strong>
            </p>

            <hr>

            <ul class="list-group">
                ${ruta.puntos
                    .map(
                        (p, i) => `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${i + 1}. ${p.sucursal.nombre_comercial}</span>
                    </li>
                `,
                    )
                    .join("")}
            </ul>
        `;

        $("#tituloPanel").text("Detalle de Ruta");
        $("#panelContenido").html(html);
    });
}

function editarRuta(id) {
    $.get(route("rutas.show", id), function (ruta) {
        let html = `
            <h6>Editar Ruta</h6>

            <input type="text" class="form-control mb-2"
                id="nombreRuta"
                value="${ruta.nombre}">

            <div id="contenedorPuntos"></div>

            <button class="btn btn-sm btn-primary mb-2"
                onclick="agregarPunto()">
                Añadir punto
            </button>

            <hr>

            <h6>Tramos</h6>
            <div id="contenedorTramos"></div>

            <button class="btn btn-success w-100 mt-2"
                onclick="guardarEdicion(${ruta.id})">
                Guardar cambios
            </button>
        `;

        $("#panelContenido").html(html);

        ruta.puntos.forEach((p) => {
            agregarPunto(p.sucursal_id);
        });

        setTimeout(() => {
            generarTramos(ruta.tramos);
        }, 100);

        lucide.createIcons();
    });
}

function eliminarPuntoEdit(puntoId) {
    Swal.fire({
        title: "¿Eliminar punto?",
        icon: "warning",
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route("ruta.punto.delete", puntoId),
                method: "DELETE",
                data: {
                    _token: $("meta[name=csrf-token]").attr("content"),
                },
                success: function () {
                    Swal.fire("Eliminado", "", "success");
                },
            });
        }
    });
    actualizarOpciones();
}

$(document).on("click", ".ver", function () {
    let id = $(this).data("id");
    verRuta(id);
});

$(document).on("click", ".editar", function () {
    let id = $(this).data("id");
    editarRuta(id);
});

$(document).on("click", ".desactivar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Desactivar ruta?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(route("rutas.desactivar", id), {
                _token: $("meta[name=csrf-token]").attr("content"),
            }).done(function () {
                Swal.fire("Desactivado", "", "success");
                tablaRutas.ajax.reload();
            });
        }
    });
});

window.guardarEdicion = function (id) {
    let nombre = $("#nombreRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos select").each(function () {
        let val = $(this).val();
        if (val) {
            puntos.push(val);
        }
    });

    $("input[name='duracion[]']").each(function () {
        duracion.push($(this).val());
    });

    $("input[name='costo[]']").each(function () {
        costo.push($(this).val());
    });

    if (nombre.trim() === "") {
        Swal.fire("Error", "El nombre es obligatorio", "error");
        return;
    }

    if (puntos.length < 2) {
        Swal.fire("Error", "Debe tener al menos 2 puntos", "error");
        return;
    }

    Swal.fire({
        title: "Actualizando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: route("rutas.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
            _method: "PUT",
            nombre: nombre,
            puntos: puntos,
            duracion: duracion,
            costo: costo,
        },
        success: function () {
            Swal.fire("Actualizado", "", "success");

            tablaRutas.ajax.reload();

            $("#panelContenido").html(
                '<p class="text-muted">Selecciona una ruta</p>',
            );
        },
        error: function (err) {
            let mensaje = "Error al actualizar";

            if (err.responseJSON?.message) {
                mensaje = err.responseJSON.message;
            }

            Swal.fire("Error", mensaje, "error");
        },
    });
};

function generarTramos(tramosData = []) {
    let puntos = [];

    $("#contenedorPuntos select").each(function () {
        let val = $(this).val();
        let text = $(this).find("option:selected").text();

        if (val) {
            puntos.push({
                id: val,
                nombre: text,
            });
        }
    });

    let html = "";

    for (let i = 0; i < puntos.length - 1; i++) {
        let duracion = tramosData[i]?.duracion ?? "";
        let costo = tramosData[i]?.costo ?? "";

        html += `
            <div class="mb-2 border p-3 rounded">

                <strong>${puntos[i].nombre} → ${puntos[i + 1].nombre}</strong>

                <div class="row mt-2">

                    <div class="col-6">
                        <input type="number"
                            name="duracion[]"
                            class="form-control"
                            value="${duracion}"
                            placeholder="Minutos">
                    </div>

                    <div class="col-6">
                        <input type="number"
                            name="costo[]"
                            class="form-control"
                            value="${costo}"
                            placeholder="Costo">
                    </div>

                </div>
            </div>
        `;
    }

    $("#contenedorTramos").html(html);
}
