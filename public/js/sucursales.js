$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    // ==============================
    // 🧾 Inicializar DataTable
    // ==============================
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
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    // ==============================
    // 🌎 Cargar ubigeos dinámicamente
    // ==============================
    function cargarDepartamentos(selected = null) {
        $.get("/ubigeos/departamentos", function (departamentos) {
            let $select = $("#departamento_id");
            $select.empty().append('<option value="">Seleccione</option>');
            departamentos.forEach((d) =>
                $select.append(`<option value="${d.id}">${d.nombre}</option>`)
            );
            if (selected) $select.val(selected);
        });
    }

    function cargarProvincias(departamento_id, selected = null) {
        if (!departamento_id) return;
        $.get(`/ubigeos/provincias/${departamento_id}`, function (provincias) {
            let $select = $("#provincia_id");
            $select.empty().append('<option value="">Seleccione</option>');
            provincias.forEach((p) =>
                $select.append(`<option value="${p.id}">${p.nombre}</option>`)
            );
            if (selected) $select.val(selected);
        });
    }

    function cargarDistritos(provincia_id, selected = null) {
        if (!provincia_id) return;
        $.get(`/ubigeos/distritos/${provincia_id}`, function (distritos) {
            let $select = $("#distrito_id");
            $select.empty().append('<option value="">Seleccione</option>');
            distritos.forEach((d) =>
                $select.append(`<option value="${d.id}">${d.nombre}</option>`)
            );
            if (selected) $select.val(selected);
        });
    }

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

    // ==============================
    // ➕ Nueva sucursal
    // ==============================
    $("#btnNuevaSucursal").click(() => {
        $("#formSucursal")[0].reset();
        $("#sucursal_id").val("");
        $("#modalTitulo").text("Registrar Sucursal");

        // cargar los ubigeos
        cargarDepartamentos();

        $("#modalSucursal").modal("show");
    });

    // ==============================
    // 💾 Guardar sucursal
    // ==============================
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

    // ==============================
    // ✏️ Editar sucursal
    // ==============================
    $("#tablaSucursales").on("click", ".editar", function () {
        const id = $(this).data("id");

        $.get(`/sucursales/detalle/${id}`, function (data) {
            $("#sucursal_id").val(data.id);
            $("#empresa_id").val(data.empresa_id);
            $('input[name="nombre_comercial"]').val(data.nombre_comercial);
            $('input[name="direccion"]').val(data.direccion);
            $('input[name="telefono"]').val(data.telefono);

            $("#modalTitulo").text("Editar Sucursal");

            cargarDepartamentos(data.departamento_id);
            cargarProvincias(data.departamento_id, data.provincia_id);
            cargarDistritos(data.provincia_id, data.distrito_id);

            $("#modalSucursal").modal("show");
        });
    });

    // ==============================
    // 👁️ Ver detalles
    // ==============================
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
