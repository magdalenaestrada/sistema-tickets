$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(function () {
    // =======================
    // 🧾 Inicializar DataTable
    // =======================

    let tabla = $("#tablaCargos").DataTable({
        ajax: "/cargos/datatable",
        columns: [
            { title: "ID", data: "id" },
            { title: "Descripcion", data: "descripcion" },
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
    });

    // =======================
    // ➕ Nuevo registro
    // =======================
    $("#btnNuevaCargo").click(function () {
        $("#formCargo")[0].reset();
        $("#cargo_id").val("");
        $("#descripcion").val("");
        $("#modalTitulo").text("Registrar Cargo");
        $("#btnGuardarCargo").text("Guardar");
        $("#modalCargo").modal("show");
    });

    // =======================
    // ✏️ Editar registro
    // =======================
    $("#tablaCargos").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(`/cargos/${id}`, function (data) {
            $("#cargo_id").val(id);
            $("#descripcion").val(data.descripcion);
            $("#modalTitulo").text("Editar Cargo");
            $("#btnGuardarCargo").text("Actualizar");
            $("#modalCargo").modal("show");
        });
    });

    // =======================
    // 👁️ Ver registro
    // =======================
    $("#tablaCargos").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(`/cargos/${id}`, function (data) {
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

    // =======================
    // 🗑️ Eliminar registro
    // =======================
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
                    url: `/cargos/${id}`,
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

    // --- Guardar cargo (crear o actualizar) ---
    $("#formCargo").on("submit", function (e) {
        e.preventDefault();

        const id = $("#cargo_id").val();
        const url = id ? `/cargos/${id}` : `/cargos`;
        const method = id ? "POST" : "POST"; // siempre POST, y enviamos _method si es update

        // Incluimos el _method si es edición
        let formData = $(this).serializeArray();
        if (id) {
            formData.push({ name: "_method", value: "PUT" });
        }

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
