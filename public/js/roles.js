$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).on("click", ".asignar-permisos", function () {
    let rolId = $(this).data("id");

    let url = route("roles.permisos", { rol: rolId });

    window.location.href = url;
});
$(function () {
    let tabla = $("#tablaRoles").DataTable({
        ajax: route("roles.datatable"), // ← cambio a Ziggy
        columns: [
            { title: "ID", data: "id" },
            { title: "Nombre", data: "name" },
            {
                title: "Acciones",
                data: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
        responsive: false,
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
        dom: "rtip",
    });

    $("#btnNuevaRol").click(function () {
        $("#formRol")[0].reset();
        $("#rol_id").val("");
        $("#name").val("");
        $("#modalTitulo").text("Registrar Rol");
        $("#btnGuardarRol").text("Guardar");
        $("#modalRol").modal("show");
    });

    $("#tablaRoles").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(route("roles.mostrar", id), function (data) {
            $("#rol_id").val(id);
            $("#name").val(data.name);
            $("#modalTitulo").text("Editar Rol");
            $("#btnGuardarRol").text("Actualizar");
            $("#modalRol").modal("show");
        });
    });

    $("#tablaRoles").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(route("roles.mostrar", id), function (data) {
            // ← Ziggy
            Swal.fire({
                title: "Detalles de Rol",
                html: `
                <div style="text-align: left;">
                    <b> Descripcion:</b> ${data.name}<br>
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

    $("#tablaRoles").on("click", ".eliminar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar rol?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("roles.eliminar", id), // ← Ziggy
                    type: "DELETE",
                    success: function (res) {
                        if (res.success) {
                            $("#tablaRoles")
                                .DataTable()
                                .ajax.reload(null, false);
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "No se puede eliminar",
                                text: res.message,
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el rol. Ver consola para más detalles.",
                        });
                    },
                });
            }
        });
    });

    $("#formRol").on("submit", function (e) {
        e.preventDefault();

        const id = $("#rol_id").val();
        const url = id ? route("roles.actualizar", id) : route("roles.guardar");
        const method = id ? "PUT" : "POST";

        let formData = $(this).serializeArray();

        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    $("#modalRol").modal("hide");
                    $("#tablaRoles").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message || "Rol guardado correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: res.message || "Ocurrió un error al guardar.",
                    });
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar el registro. Revisa la consola.",
                });
            },
        });
    });
});
