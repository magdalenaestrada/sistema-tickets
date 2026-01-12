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
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            lucide.createIcons();
        },
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
});
