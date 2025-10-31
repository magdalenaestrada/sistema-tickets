$(document).ready(function () {
    const modal = new bootstrap.Modal($("#modalEmpleado")[0]);

    // Inicializar DataTable
    const tabla = $("#tablaEmpleados").DataTable({
        ajax: "/empleados/datatable",
        columns: [
            { data: "id" },
            { data: "documento" },
            { data: "nombre" },
            { data: "area" },
            { data: "sucursal" },
            { data: "cargo" },
            { data: "acciones", orderable: false, searchable: false },
        ],
    });

    function cargarListasEmpleado(callback = null) {
        $.get("/listas", function (res) {
            // Llenar select de Áreas
            const areaSelect = $("#area_id");
            areaSelect
                .empty()
                .append('<option value="">Seleccione un área</option>');
            res.areas.forEach((area) => {
                areaSelect.append(
                    `<option value="${area.id}">${area.descripcion}</option>`
                );
            });

            // Llenar select de Cargos
            const cargoSelect = $("#cargo_id");
            cargoSelect
                .empty()
                .append('<option value="">Seleccione un cargo</option>');
            res.cargos.forEach((cargo) => {
                cargoSelect.append(
                    `<option value="${cargo.id}">${cargo.descripcion}</option>`
                );
            });

            // Llenar select de Sucursales
            const sucursalSelect = $("#sucursal_id");
            sucursalSelect
                .empty()
                .append('<option value="">Seleccione una sucursal</option>');
            res.sucursales.forEach((sucursal) => {
                sucursalSelect.append(
                    `<option value="${sucursal.id}">${sucursal.nombre_comercial}</option>`
                );
            });
            const tipoDocumentoSelect = $("#tipo_documento_id");
            tipoDocumentoSelect
                .empty()
                .append('<option value="">Seleccione</option>');
            res.tipos_documento.forEach((tipo_documento) => {
                tipoDocumentoSelect.append(
                    `<option value="${tipo_documento.id}">${tipo_documento.codigo}</option>`
                );
            });

            if (callback) callback();
        }).fail(() => {
            alert("Error al cargar las listas.");
        });
    }

    // 🔹 Nuevo empleado
    $("#btnNuevoEmpleado").click(function () {
        $("#formEmpleado")[0].reset();
        $("#empleado_id").val("");
        $("#seccionUsuario").hide();
        $("#chkUsuario").prop("checked", false);
        $("#area_id, #cargo_id, #sucursal_id").val(null).trigger("change");

        cargarListasEmpleado(() => {
            modal.show();
        });
    });

    $("#tablaEmpleados").on("click", ".editar", function () {
        const id = $(this).data("id");

        // Primero cargar las listas
        cargarListasEmpleado(() => {
            // Luego traer datos del empleado
            $.get(`/empleados/${id}`, function (res) {
                $("#empleado_id").val(res.id);
                $("#documento").val(res.persona.documento);
                $("#nombres").val(res.persona.nombres);
                $("#apellidos").val(res.persona.apellidos);
                $("#correo").val(res.persona.correo);
                $("#telefono").val(res.persona.telefono);
                $("#direccion").val(res.persona.direccion);
                $("#area_id").val(res.area_id);
                $("#sucursal_id").val(res.sucursal_id);
                $("#cargo_id").val(res.cargo_id);

                modal.show();
            });
        });
    });
});
