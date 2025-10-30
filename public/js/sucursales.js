$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    let tabla = $("#tablaSucursales").DataTable({
        ajax: `/sucursales/${EMPRESA_ID}/datatable`,
        columns: [
            { title: "ID", data: "id" },
            { title: "Distrito", data: "distrito" },
            { title: "Nombre Comercial", data: "nombre_comercial" },
            { title: "Dirección", data: "direccion" },
            { title: "Teléfono", data: "telefono" },
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
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#btnNuevaSucursal").click(() => {
        $("#formSucursal")[0].reset();
        $("#sucursal_id").val("");
        $("#modalTitulo").text("Registrar Sucursal");
        $("#modalSucursal").modal("show");
    });

    $("#formSucursal").submit(function (e) {
        e.preventDefault();

        const id = $("#sucursal_id").val();
        const url = id ? `/sucursales/${id}` : "/sucursales";
        const method = id ? "PUT" : "POST";

        let formData = $(this).serializeArray();
        formData.push({ name: "empresa_id", value: EMPRESA_ID });

        $.ajax({
            url,
            method,
            data: $.param(formData),
            success: function () {
                $("#modalSucursal").modal("hide");
                tabla.ajax.reload();
                Swal.fire(
                    "Éxito",
                    "Sucursal guardada correctamente",
                    "success"
                );
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire("Error", "No se pudo guardar", "error");
            },
        });
    });

    $("#tablaSucursales").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(`/sucursales/detalle/${id}`, function (data) {
            $("#sucursal_id").val(data.id);
            $("#empresa_id").val(data.empresa_id);
            $("#distrito_id").val(data.distrito_id);
            $('input[name="nombre_comercial"]').val(data.nombre_comercial);
            $('input[name="direccion"]').val(data.direccion);
            $('input[name="telefono"]').val(data.telefono);
            $("#modalTitulo").text("Editar Sucursal");
            $("#modalSucursal").modal("show");
        });
    });

    $("#tablaSucursales").on("click", ".ver", function () {
        const id = $(this).data("id");
        $.get(`/sucursales/detalle/${id}`, function (data) {
            Swal.fire({
                title: "Detalles de Sucursal",
                html: `
                <div style="text-align: left;">
                    <b>Empresa:</b> ${data.empresa?.razon_social || "-"}<br>
                    <b>Distrito:</b> ${data.distrito?.nombre || "-"}<br>
                    <b>Nombre Sucursal:</b> ${data.nombre_comercial || "-"}<br>
                    <b>Dirección Sucursal:</b> ${data.direccion || "-"}<br>
                    <b>Teléfono Sucursal:</b> ${data.telefono || "-"}
                </div>
            `,
                icon: "info",
                confirmButtonText: "Cerrar",
            });
        });
    });
});
