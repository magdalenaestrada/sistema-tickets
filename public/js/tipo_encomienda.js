$(function () {
    document.getElementById("btnNuevo").addEventListener("click", function () {
        document.querySelector("#modalForm input[name='descripcion']").value =
            "";
        document.querySelector("#modalForm input[name='precio_base']").value =
            "";
        document.querySelector("#modalForm input[name='peso_limite']").value =
            "";
        document.querySelector(
            "#modalForm input[name='costo_kilo_extra']"
        ).value = "";

        var myModal = new bootstrap.Modal(document.getElementById("modalForm"));
        myModal.show();
    });

    let tabla = $("#tablaTipos").DataTable({
        processing: true,
        serverSide: true,
        ajax: route("tipo-encomienda.datatable"),
        columns: [
            { data: "id" },
            { data: "descripcion" },
            { data: "precio_base" },
            { data: "peso_limite" },
            { data: "costo_kilo_extra" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#btnNuevo").on("click", function () {
        // Cargar formulario de creación por AJAX
        $.get(route("tipo-encomienda.create"), function (html) {
            $("#modalContent").html(html);
            $("#modalForm").modal("show");
        });
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
            modalEl.querySelector("input[name='peso_limite']").value =
                data.peso_limite ?? "";
            modalEl.querySelector("input[name='costo_kilo_extra']").value =
                data.costo_kilo_extra ?? "";

            modalEl.querySelector("form").action = route(
                "tipo-encomienda.update",
                id
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
                            "success"
                        );
                        // Recargar DataTable
                        $("#tablaTipos").DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire(
                            "Error!",
                            "Ocurrió un error al eliminar.",
                            "error"
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
            },
            error: function (err) {
                console.log(err);
                alert("Error al guardar.");
            },
        });
    });
    var modalEl = document.getElementById("modalForm");
    modalEl.addEventListener("hidden.bs.modal", function () {
        modalEl.querySelector("form").reset(); // resetea campos
        modalEl.querySelector("form").action = ""; // opcional, limpia la action
        modalEl.querySelector("form").method = "POST"; // opcional, pone POST por defecto
    });
});
