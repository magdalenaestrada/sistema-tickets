$(function () {
    let tabla = $("#tablaTipos").DataTable({
        processing: true,
        serverSide: true,
        ajax: route("tipo-encomienda.datatable"),
        columns: [
            { data: "id" },
            { data: "descripcion" },
            { data: "precio_base" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        dom: "rtip",
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#btnNuevo").on("click", function () {
        let form = $("#formTipoEncomienda");

        form[0].reset();
        form.attr("action", route("tipo-encomienda.store"));
        form.find("input[name='_method']").remove();

        $("#modalForm").modal("show");
    });

    var modalEl = document.getElementById("modalForm");
    var myModal = new bootstrap.Modal(modalEl);

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id"); // obtenemos el id del tipo

        modalEl.querySelector("form").reset();

        modalEl.querySelector(".modal-title").textContent =
            "Editar Tipo de Encomienda";

        $.get(route("tipo-encomienda.edit", id), function (data) {
            modalEl.querySelector("input[name='descripcion']").value =
                data.descripcion;
            modalEl.querySelector("input[name='precio_base']").value =
                data.precio_base;
            modalEl.querySelector("form").action = route(
                "tipo-encomienda.update",
                id,
            );
            modalEl.querySelector("form").method = "POST";

            let methodInput = modalEl.querySelector("input[name='_method']");
            if (!methodInput) {
                methodInput = document.createElement("input");
                methodInput.type = "hidden";
                methodInput.name = "_method";
                modalEl.querySelector("form").appendChild(methodInput);
            }
            methodInput.value = "PUT";

            myModal.show();
        });
    });

    $(document).on("click", ".eliminar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Estás seguro?",
            text: "¡No podrás revertir esto!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                // Petición AJAX para eliminar
                $.ajax({
                    url: route("tipo-encomienda.destroy", id),
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Swal.fire(
                            "Eliminado!",
                            "El tipo de encomienda ha sido eliminado.",
                            "success",
                        );
                        // Recargar DataTable
                        $("#tablaTipos").DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire(
                            "No se pudo eliminar",
                            xhr.responseJSON?.message ||
                                "Ocurrió un error inesperado.",
                            "error",
                        );
                    },
                });
            }
        });
    });

    $(document).on("submit", "#formTipoEncomienda", function (e) {
        e.preventDefault();

        let form = $(this);

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: form.serialize(),

            success: function (res) {
                $("#modalForm").modal("hide");
                $("#tablaTipos").DataTable().ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: res.message,
                });
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    if (errors.descripcion) {
                        Swal.fire({
                            icon: "warning",
                            title: "Atención",
                            text: "Ya existe un tipo de encomienda con esta descripción.",
                        });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error inesperado.",
                    });
                }
            },
        });
    });
    var modalEl = document.getElementById("modalForm");
});
