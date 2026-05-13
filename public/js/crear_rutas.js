let sucursales = [];

$(document).ready(async function () {
    sucursales = await $.get(route("sucursales.lista"));
    agregarPunto();
    agregarPunto();
});

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

function agregarPunto() {
    let options = obtenerOpcionesDisponibles();

    let index = $("#contenedorPuntos .punto").length + 1;

    let html = `
<div class="punto border rounded p-3 mb-3">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge bg-secondary">${index}</span>

        <button type="button"
            class="btn btn-danger btn-sm"
            onclick="eliminarPunto(this)">
            <i data-lucide="trash"></i>
        </button>
    </div>

    <div class="row">

        <div class="col-md-3">
            <label>Departamento</label>
            <select class="form-select departamento"
                name="puntos[${index - 1}][departamento_id]"
                required>
                <option value="">Seleccione</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Provincia</label>
            <select class="form-select provincia"
                name="puntos[${index - 1}][provincia_id]"
                required>
                <option value="">Seleccione</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Distrito</label>
            <select class="form-select distrito"
                name="puntos[${index - 1}][distrito_id]"
                required>
                <option value="">Seleccione</option>
            </select>
        </div>
        <div class="col-md-3">
    <label>Pueblito</label>
    <select class="form-select pueblito"
        name="puntos[${index - 1}][pueblito_id]"
        required>
        <option value="">Seleccione</option>
    </select>
</div>

        <div class="col-md-3">
            <label>Sucursal (opcional)</label>
            <select class="form-select sucursal"
                name="puntos[${index - 1}][sucursal_id]">
                <option value="">Sin sucursal</option>
            </select>
        </div>

    </div>
</div>
`;

    $("#contenedorPuntos").append(html);
    lucide.createIcons();

    actualizarOpciones();
}

function eliminarPunto(btn) {
    let total = $("#contenedorPuntos .punto").length;

    if (total <= 2) {
        Swal.fire("Debe tener minimo 2 puntos", "", "warning");
        return;
    }

    $(btn).closest(".punto").remove();
    reordenarPuntos();
}

function reordenarPuntos() {
    $("#contenedorPuntos .punto").each(function (i) {
        $(this)
            .find(".badge")
            .text(i + 1);
    });
}

$(document).on("change", ".distrito", async function () {
    let distritoId = $(this).val();

    let contenedor = $(this).closest(".punto");

    let pueblitoSelect = contenedor.find(".pueblito");

    pueblitoSelect.html(`<option value="">Cargando...</option>`);

    let pueblitos = await $.get(route("pueblitos.porDistrito", distritoId));

    let options = `<option value="">Seleccione</option>`;

    pueblitos.forEach((p) => {
        options += `
            <option value="${p.id}">
                ${p.nombre}
            </option>
        `;
    });

    pueblitoSelect.html(options);
});

$("#formRuta").submit(function (e) {
    e.preventDefault();

    if (!validarPuntos()) {
        Swal.fire("Error", "No puedes repetir destinos", "error");
        return;
    }

    let data = $(this).serialize();

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("rutas.store"), data)
        .done(function () {
            Swal.fire({
                icon: "success",
                title: "Ruta creada",
                timer: 1500,
                showConfirmButton: false,
            });

            setTimeout(() => {
                window.location = route("rutas.index");
            }, 1500);
        })
        .fail(function (err) {
            let mensaje = "Error al guardar";

            if (err.responseJSON?.message) {
                mensaje = err.responseJSON.message;
            }

            Swal.fire("Error", mensaje, "error");
        });
});

function validarPuntos() {
    let valores = [];

    let valido = true;

    $("#contenedorPuntos .pueblito").each(function () {
        let val = $(this).val();

        if (!val) {
            valido = false;
            return;
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
});
