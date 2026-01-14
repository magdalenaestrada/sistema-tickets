$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(function () {
    let tabla = $("#tablaCargos").DataTable({
        ajax: route("cargos.datatable"), // ← cambio a Ziggy
        columns: [
            { title: "ID", data: "id" },
            { title: "Descripcion", data: "descripcion" },
            { title: "Rol", data: "rol" },
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
        dom:'rtip'
    });

    $("#btnNuevaCargo").click(function () {
        $("#formCargo")[0].reset();
        $("#cargo_id").val("");
        $("#descripcion").val("");
        $("#modalTitulo").text("Registrar Cargo");
        $("#btnGuardarCargo").text("Guardar");
        $("#modalCargo").modal("show");
    });

    $("#tablaCargos").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(route("cargos.mostrar", id), function (data) {
            $("#cargo_id").val(id);
            $("#descripcion").val(data.descripcion);
            $("#rol_id").val(data.rol_id); 
            $("#modalTitulo").text("Editar Cargo");
            $("#btnGuardarCargo").text("Actualizar");
            $("#modalCargo").modal("show");
        });
    });

    $("#tablaCargos").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(route("cargos.mostrar", id), function (data) {
            Swal.fire({
                title: "Detalles de Cargo",
                html: `
                <div style="text-align: left;">
                    <b> Descripcion:</b> ${data.descripcion}<br>
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

    $("#tablaCargos").on("click", ".eliminar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar cargo?",
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
                    url: route("cargos.eliminar", id),
                    type: "DELETE",
                    success: function (res) {
                        if (res.success) {
                            $("#tablaCargos")
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
                            text: "No se pudo eliminar el cargo. Ver consola para más detalles.",
                        });
                    },
                });
            }
        });
    });

    $("#formCargo").on("submit", function (e) {
        e.preventDefault();

        const id = $("#cargo_id").val();
        const url = id
            ? route("cargos.actualizar", id)
            : route("cargos.guardar");
        const method = id ? "PUT" : "POST";

        let formData = $(this).serializeArray();

        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    $("#modalCargo").modal("hide");
                    $("#tablaCargos").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message || "Cargo guardado correctamente",
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
