$(function () {
    let tabla = $("#tablaUsuarios").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: route("usuarios.datatable"),
            data: function (d) {
                d.empleado = $("#filtroEmpleado").val();
                d.username = $("#filtroUsuario").val();
            },
        },
        dom: "rtip",
        columns: [
            { data: "id" },
            { data: "empleado" },
            { data: "username" },
            { data: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    document
        .getElementById("filtroEmpleado")
        .addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
        });

    document
        .getElementById("filtroUsuario")
        .addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, "");
        });
    $("#filtroEmpleado, #filtroUsuario").on("keyup", function () {
        tabla.ajax.reload();
    });

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id");

        $.get(route("usuarios.mostrar", id), function (data) {
            $("#usuario_id").val(data.id);
            $("#username").val(data.username);

            $("#persona_documento").val(data.persona.documento);
            $("#persona_nombre").val(data.persona.nombre);

            $("#modalUsuario").modal("show");
        });
    });

    $(document).on("click change", "#chkUsuario", function (e) {
        e.preventDefault();
        $(this).prop("checked", true);
        return false;
    });

    $("#modalEmpleado").on("shown.bs.modal", function () {
        $("#chkUsuario").prop("checked", true);
        $("#usuario, #password").attr("required", "required");
    });

    $("#filtroNombres").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });

    $(function () {
        $("#seccionUsuario").removeAttr("hidden").show();
        const observer = new MutationObserver(() => {
            if (
                $("#seccionUsuario").is("[hidden]") ||
                $("#seccionUsuario").is(":hidden")
            ) {
                $("#seccionUsuario").removeAttr("hidden").show();
            }
        });

        observer.observe(document.getElementById("seccionUsuario"), {
            attributes: true,
            attributeFilter: ["hidden", "style"],
        });
    });

    $("#formUsuario").submit(function (e) {
        e.preventDefault();

        let id = $("#usuario_id").val();

        $.ajax({
            url: route("usuarios.actualizar", id),
            type: "PUT",
            data: $(this).serialize(),
            success: function () {
                $("#modalUsuario").modal("hide");
                tabla.ajax.reload(null, false);
            },
        });
    });

    $(document).on("empleado:guardado", function () {
        if ($.fn.DataTable.isDataTable("#tablaUsuarios")) {
            $("#tablaUsuarios").DataTable().ajax.reload(null, false);
        }
    });

    $(document).on("click", ".desactivar", function () {
        let id = $(this).data("id");

        Swal.fire({
            icon: "warning",
            title: "¿Desactivar usuario?",
            text: "¿Está seguro que quiere desactivar este usuario?",
            showCancelButton: true,
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("usuarios.desactivar", id),
                    type: "PUT",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        tabla.ajax.reload(null, false);
                        Swal.fire(
                            "Éxito",
                            "Usuario desactivado correctamente",
                            "success",
                        );
                    },
                });
            }
        });
    });

    $(document).on("click", ".activar", function () {
        let id = $(this).data("id");

        Swal.fire({
            icon: "warning",
            title: "¿Activar usuario?",
            text: "¿Está seguro que quiere volver a activar este usuario?",
            showCancelButton: true,
            confirmButtonText: "Sí, activar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("usuarios.activar", id),
                    type: "PUT",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        tabla.ajax.reload(null, false);
                        Swal.fire(
                            "Éxito",
                            "Usuario activado correctamente",
                            "success",
                        );
                    },
                });
            }
        });
    });
});
