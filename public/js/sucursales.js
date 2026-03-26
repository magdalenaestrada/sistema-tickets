$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
let UBIGEO = null;
$(document).ready(async function () {
    UBIGEO = await $.get(route("ubigeos.todo"));
    cargarSelectDepartamentos();

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

        drawCallback: function () {
            lucide.createIcons();
        },
        dom: "rtip",
    });

    function cargarSelectDepartamentos() {
        const $dep = $("#departamento_id");
        $dep.empty().append('<option value="">Seleccione</option>');

        UBIGEO.forEach((dep) => {
            $dep.append(`<option value="${dep.id}">${dep.nombre}</option>`);
        });
    }
    function cargarSelectProvincias(depId) {
        const $prov = $("#provincia_id");
        $prov.empty().append('<option value="">Seleccione</option>');

        const dep = UBIGEO.find((d) => d.id == depId);
        if (!dep) return;

        dep.provincias.forEach((p) => {
            $prov.append(`<option value="${p.id}">${p.nombre}</option>`);
        });
    }
    function cargarSelectDistritos(depId, provId) {
        const $dist = $("#distrito_id");
        $dist.empty().append('<option value="">Seleccione</option>');

        const dep = UBIGEO.find((d) => d.id == depId);
        const prov = dep?.provincias.find((p) => p.id == provId);
        if (!prov) return;

        prov.distritos.forEach((d) => {
            $dist.append(`<option value="${d.id}">${d.nombre}</option>`);
        });
    }

    $("#departamento_id").on("change", function () {
        cargarSelectProvincias(this.value);
        $("#distrito_id")
            .empty()
            .append('<option value="">Seleccione</option>');
    });

    $("#provincia_id").on("change", function () {
        cargarSelectDistritos($("#departamento_id").val(), this.value);
    });

    function setUbigeo(depId, provId, distId) {
        $("#departamento_id").val(depId).trigger("change");
        $("#provincia_id").val(provId).trigger("change");
        $("#distrito_id").val(distId);
    }

    $("#btnNuevaSucursal").click(() => {
        $("#formSucursal")[0].reset();
        $("#sucursal_id").val("");
        $("#modalTitulo").text("Registrar Sucursal");

        cargarSelectDepartamentos();
        $("#provincia_id")
            .empty()
            .append('<option value="">Seleccione</option>');
        $("#distrito_id")
            .empty()
            .append('<option value="">Seleccione</option>');

        $("#modalSucursal").modal("show");
    });

    $(document).on("click", ".editar", function () {
        const id = $(this).data("id");

        $.get(route("sucursales.detalle", id), function (data) {
            $("#sucursal_id").val(data.id);
            $("#nombre_comercial_sucursal").val(data.nombre_comercial);
            $("#direccion_sucursal").val(data.direccion);
            $("#telefono").val(data.telefono);
            $("#modalTitulo").text("Editar Sucursal");

            cargarSelectDepartamentos();
            setUbigeo(
                data.departamento_id,
                data.provincia_id,
                data.distrito_id,
            );

            $("#modalSucursal").modal("show");
        });
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
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            Swal.fire({
                                icon: "warning",
                                title: "No permitido",
                                text: xhr.responseJSON.message,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Ocurrió un error inesperado",
                            });
                        }
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
                    "success",
                );
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errores = xhr.responseJSON.errors;
                    let mensaje = "";

                    Object.values(errores).forEach(function (error) {
                        mensaje += error[0] + "<br>";
                    });

                    Swal.fire({
                        icon: "warning",
                        title: "Validación",
                        html: mensaje,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error inesperado",
                    });
                }
            },
        });
    });

    $("#tablaSucursales").on("click", ".ver", function () {
        const id = $(this).data("id");
        $.get(route("sucursales.detalle", id), function (data) {
            console.log(data);

            Swal.fire({
                title: "Detalles de Sucursal",
                html: `
                <div style="text-align: left;">
    <b>Empresa:</b> ${data.empresa?.razon_social || "-"}<br>
   <b>Departamento:</b> ${data.departamento?.nombre || "-"}<br>
<b>Provincia:</b> ${data.provincia?.nombre || "-"}<br>
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
