$(function () {
    // =======================
    // 🧾 Inicializar DataTable
    // =======================

    let tabla = $("#tablaEmpresas").DataTable({
        ajax: "/empresas/datatable",
        columns: [
            { title: "ID", data: "id" },
            { title: "Documento", data: "documento" },
            { title: "Razón Social", data: "razon_social" },
            { title: "Nombre Comercial", data: "nombre_comercial" },
            { title: "Dirección", data: "direccion" },
            {
                title: "Acciones",
                data: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        responsive: false,
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    // =======================
    // ➕ Nuevo registro
    // =======================
    $("#btnNuevaEmpresa").click(function () {
        $("#formEmpresa")[0].reset();
        $("#empresa_id").val("");
        $("#usuario_facturacion").val("");
        $("#contrasena_facturacion").val("");
        $("#modalTitulo").text("Registrar Empresa");
        $("#btnGuardarEmpresa").text("Guardar");
        $("#modalEmpresa").modal("show");
    });

    // =======================
    // ✏️ Editar registro
    // =======================
    $("#tablaEmpresas").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(`/empresas/${id}`, function (data) {
            $("#empresa_id").val(data.id);
            $("#documento").val(data.documento);
            $("#razon_social").val(data.razon_social);
            $("#nombre_comercial").val(data.nombre_comercial);
            $("#direccion").val(data.direccion);
            $("#usuario_facturacion").val(data.usuario_facturacion);
            $("#contrasena_facturacion").val(data.contrasena_facturacion);
            $("#modalTitulo").text("Editar Empresa");
            $("#btnGuardarEmpresa").text("Actualizar");
            $("#modalEmpresa").modal("show");
        });
    });

    // =======================
    // 👁️ Ver registro
    // =======================
    $("#tablaEmpresas").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(`/empresas/${id}`, function (data) {
            Swal.fire({
                title: "Detalles de Empresa",
                html: `
                <div style="text-align: left;">
                    <b> Documento:</b> ${data.documento}<br>
                    <b> Razón Social:</b> ${data.razon_social}<br>
                    <b> Nombre Comercial:</b> ${
                        data.nombre_comercial || "-"
                    }<br>
                    <b> Dirección:</b> ${data.direccion || "-"}
                </div>
            `,
                icon: "info",
                confirmButtonText: "Cerrar",
                customClass: {
                    popup: "swal-left-text",
                },
            });
        });
    });

    // =======================
    // 🔍 Buscar RUC o DNI
    // =======================
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
            url: `/buscar/?documento=${documento}`,
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
                    $('input[name="razon_social"]').val(data.razon_social);
                    $('input[name="nombre_comercial"]').val(
                        data.nombre_comercial || ""
                    );
                    $('input[name="direccion"]').val(data.direccion || "");
                } else if (data.nombres) {
                    $('input[name="razon_social"]').val(
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
                    .html('<i class="link-icon" data-lucide="search"></i> ');
                lucide.createIcons();
            },
        });
    });
    // --- Guardar empresa (crear o actualizar) ---
    $("#formEmpresa").on("submit", function (e) {
        e.preventDefault(); // 🔴 evita la recarga de la página

        let formData = $(this).serialize();
        let id = $("#empresa_id").val();
        let url = id ? `/empresas/${id}` : `/empresas`;
        let method = id ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function (res) {
                if (res.success) {
                    $("#modalEmpresa").modal("hide");
                    $("#tablaEmpresas").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Guardado",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: res.message,
                    });
                }
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar la empresa. Ver consola para más detalles.",
                });
            },
        });
    });
});
// Escucha los clics en los botones generados dinámicamente
$(document).on("click", ".ver", function () {
    const id = $(this).data("id");
    console.log("Ver empresa ID:", id);
});

$(document).on("click", ".editar", function () {
    const id = $(this).data("id");
    console.log("Editar empresa ID:", id);
});
// Ir a sucursales de una empresa
$("#tablaEmpresas").on("click", ".sucursales", function () {
    const id = $(this).data("id");
    window.location.href = `/sucursales/${id}`;
});
