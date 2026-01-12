$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    let tabla = $("#tablaSucursales").DataTable({
        ajax: route("sucursales.datatable", EMPRESA_ID),
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
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });
    function cargarDepartamentos(selected = null, callback = null) {
        $.get(route("ubigeos.departamentos"), function (departamentos) {
            let $select = $("#departamento_id");
            $select.empty().append('<option value="">Seleccione</option>');

            departamentos.forEach((d) =>
                $select.append(`<option value="${d.id}">${d.nombre}</option>`)
            );

            if (selected) {
                $select.val(String(selected)).trigger("change");
            }

            if (callback) callback();
        });
    }

    function cargarProvincias(
        departamento_id,
        selected = null,
        callback = null
    ) {
        if (!departamento_id) return;

        $.get(
            route("ubigeos.provincias", departamento_id),
            function (provincias) {
                let $select = $("#provincia_id");
                $select.empty().append('<option value="">Seleccione</option>');

                provincias.forEach((p) =>
                    $select.append(
                        `<option value="${p.id}">${p.nombre}</option>`
                    )
                );

                if (selected) {
                    $select.val(String(selected)).trigger("change");
                }

                if (callback) callback();
            }
        );
    }
    function cargarDistritos(provincia_id, selected = null) {
        if (!provincia_id) return;

        $.get(route("ubigeos.distritos", provincia_id), function (distritos) {
            let $select = $("#distrito_id");
            $select.empty().append('<option value="">Seleccione</option>');

            distritos.forEach((d) =>
                $select.append(`<option value="${d.id}">${d.nombre}</option>`)
            );

            if (selected) {
                $select.val(String(selected));
            }
        });
    }

    function vincularEventosUbigeo() {
        $("#departamento_id").off("change");
        $("#provincia_id").off("change");

        $("#departamento_id").on("change", function () {
            const id = $(this).val();
            $("#provincia_id")
                .empty()
                .append('<option value="">Seleccione</option>');
            $("#distrito_id")
                .empty()
                .append('<option value="">Seleccione</option>');
            cargarProvincias(id);
        });

        $("#provincia_id").on("change", function () {
            const id = $(this).val();
            $("#distrito_id")
                .empty()
                .append('<option value="">Seleccione</option>');
            cargarDistritos(id);
        });
    }

    $("#btnNuevaSucursal").click(() => {
        $("#formSucursal")[0].reset();
        $("#sucursal_id").val("");
        $("#modalTitulo").text("Registrar Sucursal");

        cargarDepartamentos();
        vincularEventosUbigeo(); // Vincular eventos para nuevo registro
        $("#modalSucursal").modal("show");
    });

    $(document).on("click", ".activar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Activar sucursal?",
            text: "La sucursal volverá a estar disponible.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, activar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#28a745",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("sucursales.activar", id),
                    type: "PATCH",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: "Activada",
                            text: "La sucursal fue activada correctamente",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        $("#tablaSucursales")
                            .DataTable()
                            .ajax.reload(null, false);
                    },
                });
            }
        });
    });

    $(document).on("click", ".desactivar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Desactivar sucursal?",
            text: "No podrás usar esta sucursal mientras esté inactiva.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#dc3545",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("sucursales.desactivar", id),
                    type: "PATCH",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: "Desactivada",
                            text: "La sucursal fue desactivada correctamente",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        $("#tablaSucursales")
                            .DataTable()
                            .ajax.reload(null, false);
                    },
                });
            }
        });
    });

    $("#formSucursal").submit(function (e) {
        e.preventDefault();

        const id = $("#sucursal_id").val();
        const url = id
            ? route("sucursales.actualizar", id)
            : route("sucursales.guardar");
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

        $.get(route("sucursales.detalle", id), function (data) {
            console.log("Datos recibidos:", data);
            console.log(
                "DEP:",
                data.departamento_id,
                "PROV:",
                data.provincia_id,
                "DIST:",
                data.distrito_id
            );

            $("#sucursal_id").val(data.id);
            $('input[name="nombre_comercial"]').val(data.nombre_comercial);
            $('input[name="direccion"]').val(data.direccion);
            $('input[name="telefono"]').val(data.telefono);

            $("#modalTitulo").text("Editar Sucursal");

            const departamentoId =
                data.distrito?.provincia?.departamento_id ?? null;
            const provinciaId = data.distrito?.provincia_id ?? null;
            const distritoId = data.distrito_id ?? null;

            cargarDepartamentos(data.departamento_id, function () {
                cargarProvincias(
                    data.departamento_id,
                    data.provincia_id,
                    function () {
                        cargarDistritos(data.provincia_id, data.distrito_id);
                        vincularEventosUbigeo();
                        setTimeout(() => {
                            $("#modalSucursal").modal("show");
                        }, 70);
                    }
                );
            });
        });
    });

    $("#tablaSucursales").on("click", ".ver", function () {
        const id = $(this).data("id");
        $.get(route("sucursales.detalle", id), function (data) {
            Swal.fire({
                title: "Detalles de Sucursal",
                html: `
                <div style="text-align: left;">
                    <b>Empresa:</b> ${data.empresa?.razon_social || "-"}<br>
                    <b>Distrito:</b> ${data.distrito?.nombre || "-"}<br>
                    <b>Nombre Sucursal:</b> ${data.nombre_comercial || "-"}<br>
                    <b>Dirección:</b> ${data.direccion || "-"}<br>
                    <b>Teléfono:</b> ${data.telefono || "-"}
                </div>
            `,
                icon: "info",
                confirmButtonText: "Cerrar",
            });
        });
    });
});
