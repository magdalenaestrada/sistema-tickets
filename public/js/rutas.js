let tablaRutas;
let UBIGEO = [];
let sucursales = [];
let tramosActuales = [];
let pueblitos = [];

$(document).ready(async function () {
    pueblitos = await $.get(route("pueblitos.lista"));
    console.log(pueblitos);
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
            { data: "estado" },
            { data: "acciones" },
        ],

        responsive: true,
        info: false,
        dom: "rtip",

        drawCallback: function () {
            lucide.createIcons();
        },
    });

    UBIGEO = await $.get(route("ubigeos.todo"));
    sucursales = await $.get(route("sucursales.lista"));
    window.modoCrear = function () {
        let html = `
       <form id="formRuta">
    <h6 class="mb-2"><b>NOMBRE DE LA RUTA <span
                                style="color: red">*</span></b></h6>

    <input type="text" id="nombreNuevaRuta"
        class="form-control form-control-sm mb-3"
        placeholder="Nombre de la ruta" required>

    <div id="contenedorPuntos"></div>

    <button type="button" class="btn btn-sm btn-success mb-2"
        onclick="agregarPunto()">
        Añadir Punto
    </button>

    <hr>

    <h6>TRAMOS</h6>
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
            activarOrdenamiento();
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

    $("#contenedorPuntos .punto").each(function () {
        let pueblitoId = $(this).find(".pueblito").val();

        let pueblito = pueblitos.find((p) => p.id == pueblitoId);

        puntos.push({
            distrito_id: pueblito?.distrito_id || null,
            pueblito_id: pueblitoId,
            sucursal_id: $(this).find(".sucursal").val(),
        });
    });

    if (puntos.some((p) => !p.pueblito_id)) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");
        return;
    }

    $("input[name='horas[]']").each(function (index) {
        let horas = parseInt($(this).val()) || 0;
        let minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || 0;

        let total = horas * 60 + minutos;

        duracion.push(total);
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

window.agregarPunto = function (data = null) {
    let index = $("#contenedorPuntos .punto").length;

    let html = `
    <div class="punto border rounded p-3 mb-2">

        <div class="d-flex justify-content-between mb-2">

            <span class="badge bg-secondary">
                Punto ${index + 1}
            </span>

            <button type="button"
                class="btn btn-danger btn-xs"
                onclick="eliminarPunto(this)">
                <i data-lucide="trash"></i>
            </button>

        </div>

        <div class="row g-2">

            <div class="col-md-6">
                <label>PARADA</label>

                <select class="pueblito"
                    data-index="${index}">
                    <option value="">Seleccione</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>SUCURSAL</label>

                <select class=" form-control sucursal"
                    data-index="${index}">
                    <option value="">Seleccione</option>
                </select>
            </div>

        </div>

    </div>
    `;

    $("#contenedorPuntos").append(html);

    let punto = $("#contenedorPuntos .punto").last();

    let selectPueblito = punto.find(".pueblito");

    pueblitos.forEach((p) => {
        selectPueblito.append(`
            <option value="${p.id}">
                ${p.descripcion}
            </option>
        `);
    });

    let tom = new TomSelect(selectPueblito[0], {
        valueField: "id",
        labelField: "descripcion",
        searchField: ["descripcion"],

        maxOptions: 100,

        placeholder: "Buscar pueblito...",

        onChange: function (value) {
            cargarSucursalesPorPueblito(value, index);

            generarTramos(tramosActuales);
        },
    });

    if (data) {
        selectPueblito[0].tomselect.setValue(String(data.pueblito_id));

        cargarSucursalesPorPueblito(data.pueblito_id, index, data.sucursal_id);
    }

    lucide.createIcons();

    generarTramos();
};

async function cargarSucursalesPorPueblito(
    pueblitoId,
    index,
    sucursalSeleccionada = null,
) {
    let pueblito = pueblitos.find((p) => p.id == pueblitoId);

    if (!pueblito) return;

    let distritoId = pueblito.distrito_id;

    let sucursalSelect = $(`.sucursal[data-index="${index}"]`);

    sucursalSelect.empty();

    sucursalSelect.append(`
        <option value="">
            Seleccione
        </option>
    `);

    try {
        let sucursales = await $.get(
            route("ubigeos.sucursalesPorDistrito", distritoId),
        );

        sucursalSelect.empty();

        sucursalSelect.append(`
            <option value="">
                Seleccione
            </option>
        `);

        sucursales.forEach((s) => {
            sucursalSelect.append(`
                <option value="${s.id}">
                    ${s.nombre_comercial}
                </option>
            `);
        });

        if (sucursalSeleccionada) {
            sucursalSelect.val(String(sucursalSeleccionada));
        }
    } catch (error) {
        console.error(error);

        sucursalSelect.html(`
            <option value="">
                Error al cargar
            </option>
        `);
    }
}

function generarOpcionesDepartamentos() {
    let html = "";

    UBIGEO.forEach((dep) => {
        html += `
            <option value="${dep.id}">
                ${dep.nombre}
            </option>
        `;
    });

    return html;
}

$(document).on("change", ".departamento", function () {
    let index = $(this).data("index");
    let depId = $(this).val();

    let provinciaSelect = $(`.provincia[data-index="${index}"]`);
    let distritoSelect = $(`.distrito[data-index="${index}"]`);

    provinciaSelect.empty();
    distritoSelect.empty();

    provinciaSelect.append(`<option value="">Seleccione</option>`);
    distritoSelect.append(`<option value="">Seleccione</option>`);

    let dep = UBIGEO.find((d) => d.id == depId);

    if (!dep) return;

    dep.provincias.forEach((prov) => {
        provinciaSelect.append(`
            <option value="${prov.id}">
                ${prov.nombre}
            </option>
        `);
    });
});

$(document).on("change", ".provincia", function () {
    let index = $(this).data("index");

    let depId = $(`.departamento[data-index="${index}"]`).val();

    let provId = $(this).val();

    let distritoSelect = $(`.distrito[data-index="${index}"]`);

    distritoSelect.empty();

    distritoSelect.append(`<option value="">Seleccione</option>`);

    let dep = UBIGEO.find((d) => d.id == depId);

    let prov = dep?.provincias.find((p) => p.id == provId);

    if (!prov) return;

    prov.distritos.forEach((dist) => {
        distritoSelect.append(`
            <option value="${dist.id}">
                ${dist.nombre}
            </option>
        `);
    });
});

window.eliminarPunto = function (btn) {
    let total = $("#contenedorPuntos .punto").length;

    if (total <= 2) {
        Swal.fire("Debe tener minimo 2 puntos", "", "warning");
        return;
    }

    $(btn).closest(".punto").remove();
    reordenarPuntos();
    generarTramos();
};

function reordenarPuntos() {
    $("#contenedorPuntos .punto").each(function (i) {
        $(this)
            .find(".badge")
            .text(i + 1);
    });
}

function validarPuntos() {
    let ids = new Set();

    let valido = true;

    $("#contenedorPuntos .pueblito").each(function () {
        let val = $(this).val();

        if (!val || val === "") {
            valido = false;
            return false;
        }

        val = String(val).trim();

        if (ids.has(val)) {
            valido = false;
            return false;
        }

        ids.add(val);
    });

    return valido;
}

$(document).on("change", "#contenedorPuntos select", function () {
    generarTramos(tramosActuales);
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

                        <span>
                            ${i + 1}. ${p.distrito || "Sin distrito"}
                        </span>

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
        tramosActuales = ruta.tramos;
        let html = `
            <h6>Editar Ruta</h6>

            <input type="text" class="form-control form-control-sm mb-2"
                id="nombreRuta"
                value="${ruta.nombre}">

            <div id="contenedorPuntos"></div>

            <button class="btn btn-sm btn-primary mb-2"
                onclick="agregarPunto()">
                Añadir punto
            </button>

            <hr>

            <h6>TRAMOS</h6>
            <div id="contenedorTramos"></div>

            <button class="btn btn-success w-100 mt-2"
                onclick="guardarEdicion(${ruta.id})">
                Guardar cambios
            </button>
        `;

        $("#panelContenido").html(html);

        ruta.puntos.forEach((p) => {
            agregarPunto(p);
        });

        activarOrdenamiento();
        generarTramos(ruta.tramos);

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

$(document).on("click", ".activar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Activar ruta?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, activar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(route("rutas.activar", id), {
                _token: $("meta[name=csrf-token]").attr("content"),
            }).done(function () {
                Swal.fire("Activado", "", "success");
                tablaRutas.ajax.reload();
            });
        }
    });
});

window.guardarEdicion = function (id) {
    if (puntos.some((p) => !p.pueblito_id)) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");
        return;
    }

    let nombre = $("#nombreRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos .punto").each(function () {
        let pueblito_id = $(this).find(".pueblito").val();
        let sucursal_id = $(this).find(".sucursal").val();

        let pueblito = pueblitos.find((p) => p.id == pueblito_id);

        if (pueblito_id) {
            puntos.push({
                distrito_id: pueblito?.distrito_id || null,
                pueblito_id: pueblito_id,
                sucursal_id: sucursal_id || null,
            });
        }
    });

    $("input[name='horas[]']").each(function (index) {
        let horas = parseInt($(this).val()) || 0;
        let minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || 0;

        let total = horas * 60 + minutos;

        duracion.push(total);
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
            Swal.fire(
                "Actualizado",
                "Todas la rutas asociadas será modificadas",
                "success",
            );

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

function minutosAHorasMinutos(total) {
    total = parseInt(total) || 0;

    return {
        horas: Math.floor(total / 60),
        minutos: total % 60,
    };
}

$(document).on("input", "input[name='minutos[]']", function () {
    let valor = parseInt($(this).val()) || 0;

    if (valor > 59) {
        $(this).val(59);
    }

    if (valor < 0) {
        $(this).val(0);
    }
});

function generarTramos(tramosData = []) {
    tramosActuales = tramosData;

    let puntos = [];

    $("#contenedorPuntos .punto").each(function () {
        puntos.push({
            nombre: $(this).find(".pueblito option:selected").text() || "Punto",
        });
    });

    let html = "";

    for (let i = 0; i < puntos.length - 1; i++) {
        let duracion = tramosData[i]?.duracion ?? 0;
        let costo = tramosData[i]?.costo ?? "";

        let data = minutosAHorasMinutos(duracion);

        html += `
        <div class="mb-2 border p-3 rounded">

            <strong>
                ${puntos[i].nombre}
                →
                ${puntos[i + 1].nombre}
            </strong>

            <div class="row mt-2">

                <div class="col-3">
                    <input type="number"
                        name="horas[]"
                        class="form-control"
                        value="${data.horas ?? ""}"
                        placeholder="Horas"
                        min="0">
                </div>

                <div class="col-3">
                    <input type="number"
                        name="minutos[]"
                        class="form-control"
                        value="${data.minutos ?? ""}"
                        placeholder="Min"
                        min="0"
                        max="59">
                </div>

                <div class="col-6">
                    <input type="number"
                        name="costo[]"
                        class="form-control"
                        value="${costo ?? ""}"
                        placeholder="S/ Costo">
                </div>

            </div>

        </div>
        `;
    }

    $("#contenedorTramos").html(html);
}

let sortablePuntos = null;

function activarOrdenamiento() {
    const contenedor = document.getElementById("contenedorPuntos");

    if (!contenedor) return;

    if (sortablePuntos) {
        sortablePuntos.destroy();
    }

    sortablePuntos = new Sortable(contenedor, {
        animation: 150,
        handle: ".drag-handle",
        onEnd: function () {
            reordenarPuntos();
            generarTramos();
        },
    });
}
