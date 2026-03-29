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

$("#formRuta").submit(function (e) {
    e.preventDefault();

    if (!validarPuntos()) {
        Swal.fire("Error", "No puedes repetir sucursales", "error");
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
});
