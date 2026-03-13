let empresaOriginal = {};

function guardarEstadoOriginal() {
    empresaOriginal = {
        documento: $("#documento").val(),
        razon_social: $("#razon_social").val(),
        nombre_comercial: $("#nombre_comercial").val(),
        direccion: $("#direccion").val(),
        usuario_facturacion: $("#usuario_facturacion").val(),
        contrasena_facturacion: $("#contrasena_facturacion").val(),
    };
}

$(document).ready(function () {
    guardarEstadoOriginal();
});

$(document).ready(function () {
    let rucBuscado = false;

    $("#btnBuscarRuc").prop("disabled", true);

    const empresaId = $("#empresa_id").val();

    const inputs = $(
        "#formEmpresa input[type='text'], #formEmpresa input[type='password'], #formEmpresa input[type='file']",
    );

    const btnGuardar = $("#btnGuardar");
    const btnEditar = $("#btnEditar");
    const btnCancelar = $("#btnCancelar");

    if (empresaId) {
        disableInputs();
        btnGuardar.addClass("d-none");
        btnEditar.removeClass("d-none");
    }

    btnEditar.on("click", function () {
        enableInputs();
        btnEditar.addClass("d-none");
        btnGuardar.removeClass("d-none");
        btnCancelar.removeClass("d-none");

        $("#btnBuscarRuc").prop("disabled", false);
    });

    btnCancelar.click(function () {
        Swal.fire({
            title: "¿Cancelar cambios?",
            text: "Se perderán todos los cambios realizados",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                $("#documento").val(empresaOriginal.documento);
                $("#razon_social").val(empresaOriginal.razon_social);
                $("#nombre_comercial").val(empresaOriginal.nombre_comercial);
                $("#direccion").val(empresaOriginal.direccion);
                $("#usuario_facturacion").val(
                    empresaOriginal.usuario_facturacion,
                );
                $("#contrasena_facturacion").val(
                    empresaOriginal.contrasena_facturacion,
                );
                disableInputs();
                $("#btnCancelar").addClass("d-none");
                $("#btnGuardar").addClass("d-none");
                $("#btnEditar").removeClass("d-none");
            }
        });
    });

    function guardarEmpresa() {
        let id = $("#empresa_id").val();
        let url = id
            ? route("empresas.actualizar", id)
            : route("empresas.guardar");

        let formData = new FormData($("#formEmpresa")[0]);

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,

            success: function (res) {
                if (res.success) {
                    guardarEstadoOriginal();

                    Swal.fire({
                        icon: "success",
                        title: "Guardado",
                        text: "Los datos se guardaron correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },

            error: function () {
                Swal.fire("Error", "No se pudo guardar la empresa.", "error");
            },
        });
    }

    $("#formEmpresa").on("submit", function (e) {
        e.preventDefault();

        const documento = $("#documento").val().trim();

        if (!/^\d{11}$/.test(documento)) {
            Swal.fire(
                "RUC inválido",
                "Solo puedes ingresar RUC válidos de 11 dígitos.",
                "warning",
            );
            return;
        }

        guardarEmpresa();
    });

    function disableInputs() {
        inputs.prop("disabled", true);
    }

    function enableInputs() {
        inputs.prop("disabled", false);
    }

    $("#btnBuscarRuc").on("click", function () {
        const documento = $("#documento").val().trim();
        const btn = $("#btnBuscarRuc");

        if (!/^\d{11}$/.test(documento)) {
            Swal.fire(
                "RUC inválido",
                "Solo puedes ingresar RUC válidos de 11 dígitos.",
                "warning",
            );
            return;
        }

        btn.prop("disabled", true).html(
            '<i class="link-icon" data-lucide="loader"></i>',
        );
        lucide.createIcons();

        $.ajax({
            url: route("buscar.buscar") + `?documento=${documento}`,
            type: "GET",
            dataType: "json",

            success: function (data) {
                if (data.error) {
                    Swal.fire("Error", data.error, "error");
                    return;
                }

                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                    $("#direccion").val(data.direccion || "");
                } else if (data.nombres) {
                    $("#razon_social").val(
                        `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`,
                    );
                }

                if (data.razon_social || data.nombres) {
                    rucBuscado = true;
                    $("#btnGuardar").prop("disabled", false);
                } else {
                    Swal.fire(
                        "Atención",
                        "No se encontraron datos para este RUC.",
                        "info",
                    );
                }
            },

            error: function () {
                Swal.fire(
                    "Error",
                    "Ingrese un numero de documento válido.",
                    "error",
                );
            },

            complete: function () {
                // SIEMPRE se ejecuta
                btn.prop("disabled", false).html(
                    '<i class="link-icon" data-lucide="search"></i>',
                );
                lucide.createIcons();
            },
        });
    });
});
