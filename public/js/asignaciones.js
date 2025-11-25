const modalAsignacion = new bootstrap.Modal(
    document.getElementById("modalAsignacion")
);
const tabla = $("#tablaAsignaciones");

function cargarTabla() {
    $.get("/asignaciones/list", function (data) {
        let tbody = "";
        data.forEach((a) => {
            tbody += `<tr>
                <td>${a.horario}</td>
                <td>${a.primer_conductor}</td>
                <td>${a.segundo_conductor || "-"}</td>
                <td>${a.vehiculo || "-"}</td>
                <td>
                    <button class="btn btn-sm btn-warning editar" data-id="${
                        a.id
                    }">Editar</button>
                    <button class="btn btn-sm btn-danger eliminar" data-id="${
                        a.id
                    }">Eliminar</button>
                </td>
            </tr>`;
        });
        tabla.find("tbody").html(tbody);
    });
}

$(document).ready(function () {
    cargarTabla();

    $("#btnNuevo").click(function () {
        $("#formAsignacion")[0].reset();
        $("#method").val("POST");
        $("#asignacion_id").val("");
        $("#segundo_conductor").prop("disabled", true);
        $("#modalTitulo").text("Nueva Asignación");
        modalAsignacion.show();
    });

    $("#otroConductorCheck").change(function () {
        $("#segundo_conductor").prop("disabled", !this.checked);
    });

    $("#formAsignacion").submit(function (e) {
        e.preventDefault();
        let id = $("#asignacion_id").val();
        let url = id ? `/asignaciones/${id}` : "/asignaciones";
        let method = id ? "PUT" : "POST";
        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function (res) {
                modalAsignacion.hide();
                cargarTabla();
                Swal.fire("Éxito", res.message, "success");
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "Ocurrió un error",
                    "error"
                );
            },
        });
    });

    tabla.on("click", ".editar", function () {
        let id = $(this).data("id");
        $.get(`/asignaciones/${id}`, function (a) {
            $("#asignacion_id").val(a.id);
            $("#horario_id").val(a.horario_id);
            $("#primer_conductor").val(a.primer_conductor);
            if (a.segundo_conductor) {
                $("#otroConductorCheck").prop("checked", true);
                $("#segundo_conductor")
                    .prop("disabled", false)
                    .val(a.segundo_conductor);
            } else {
                $("#otroConductorCheck").prop("checked", false);
                $("#segundo_conductor").prop("disabled", true).val("");
            }
            $("#vehiculo").val(a.vehiculo);
            $("#method").val("PUT");
            $("#modalTitulo").text("Editar Asignación");
            modalAsignacion.show();
        });
    });

    tabla.on("click", ".eliminar", function () {
        let id = $(this).data("id");
        Swal.fire({
            title: "¿Eliminar asignación?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/asignaciones/${id}`,
                    type: "DELETE",
                    success: function (res) {
                        cargarTabla();
                        Swal.fire("Eliminado", res.message, "success");
                    },
                });
            }
        });
    });

    // Evitar que el segundo conductor muestre el mismo del primero
    $("#primer_conductor").change(function () {
        let seleccionado = $(this).val();

        $("#segundo_conductor option").each(function () {
            if ($(this).val() == seleccionado && seleccionado !== "") {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        // Si el segundo conductor es igual al primero, lo limpiamos
        if ($("#segundo_conductor").val() == seleccionado) {
            $("#segundo_conductor").val("");
        }
    });
});
