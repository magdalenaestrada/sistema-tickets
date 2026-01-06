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
    const empresaId = $("#empresa_id").val();

    // SOLO los inputs de texto, excepto _token y empresa_id
    const inputs = $(
        "#formEmpresa input[type='text'], #formEmpresa input[type='password'], #formEmpresa input[type='file']"
    );

    const btnGuardar = $("#btnGuardar");
    const btnEditar = $("#btnEditar");
    const btnCancelar = $("#btnCancelar");

    // Si existe empresa → bloquear inputs
    if (empresaId) {
        disableInputs();
        btnGuardar.addClass("d-none");
        btnEditar.removeClass("d-none");
    }

    // Botón editar → habilitar inputs
    btnEditar.on("click", function () {
        enableInputs();
        btnEditar.addClass("d-none");
        btnGuardar.removeClass("d-none");
        btnCancelar.removeClass("d-none");
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
                    empresaOriginal.usuario_facturacion
                );
                $("#contrasena_facturacion").val(
                    empresaOriginal.contrasena_facturacion
                );
                disableInputs();
                $("#btnCancelar").addClass("d-none");
                $("#btnGuardar").addClass("d-none");
                $("#btnEditar").removeClass("d-none");

            }
        });
    });

    $("#formEmpresa").on("submit", function (e) {
        e.preventDefault();

        let id = $("#empresa_id").val();
        let url = id
            ? route("empresas.actualizar", id)
            : route("empresas.guardar");

        let formData = new FormData(this);

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,

            success: function (res) {
                if (res.success) {
                    guardarEstadoOriginal(); 
                    if (!id) {
                        $("#empresa_id").val(res.empresa.id);
                        location.reload();
                    }

                    disableInputs();
                    btnGuardar.addClass("d-none");
                    btnEditar.removeClass("d-none");

                    Swal.fire({
                        icon: "success",
                        title: "Guardado",
                        text: "Los datos se guardaron correctamente",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar la empresa.",
                });
            },
        });
    });

    // FUNCIONES
    function disableInputs() {
        inputs.prop("disabled", true);
    }

    function enableInputs() {
        inputs.prop("disabled", false);
    }

    // Buscar RUC
    $("#btnBuscarRuc").on("click", function () {
        const documento = $("#documento").val();

        if (!documento) {
            Swal.fire(
                "Atención",
                "Por favor ingrese un número de documento o RUC.",
                "warning"
            );
            return;
        }

        $("#btnBuscarRuc")
            .prop("disabled", true)
            .html('<i class="link-icon" data-lucide="search"></i>');
        lucide.createIcons();

        $.ajax({
            url: route("empresas.buscar") + `?documento=${documento}`, // ← Ziggy
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Respuesta API:", data);

                if (data.error) {
                    Swal.fire(
                        "Error",
                        "No se encontró información: " + data.error,
                        "error"
                    );
                    return;
                }

                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                    $("#nombre_comercial").val(data.nombre_comercial || "");
                    $("#direccion").val(data.direccion || "");
                } else if (data.nombres) {
                    $("#razon_social").val(
                        `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`
                    );
                } else {
                    Swal.fire(
                        "Atención",
                        "No se encontraron datos para este documento.",
                        "info"
                    );
                }
            },
            error: function (xhr) {
                console.error("Error al consultar:", xhr);
                Swal.fire(
                    "Error",
                    "Error al consultar la API. Ver consola.",
                    "error"
                );
            },
            complete: function () {
                $("#btnBuscarRuc")
                    .prop("disabled", false)
                    .html('<i class="link-icon" data-lucide="search"></i>');
                lucide.createIcons();
            },
        });
    });
});
