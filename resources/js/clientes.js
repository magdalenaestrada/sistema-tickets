let UBIGEO = null;

$(document).ready(async function () {
    UBIGEO = await $.get(route("ubigeos.todo"));
    cargarSelectDepartamentos();

    const modal = new bootstrap.Modal($("#modalCliente")[0]);

    const tabla = $("#tablaClientes").DataTable({
        serverSide: true,
        dom: "rtip",
        ajax: {
            url: route("clientes.datatable"),
            data: function (d) {
                d.tipo_documento_id = $("#filtroTipoDocumento").val();
                d.documento = $("#filtroDocumento").val();
                d.nombres = $("#filtroNombres").val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "id" },
            { data: "documento" },
            { data: "nombre" },
            { data: "telefono" },
            { data: "celular" },
            { data: "correo" },
            {
                data: "acciones",
                title: "Acciones",
                orderable: false,
                searchable: false,
            },
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: () => lucide.createIcons(),
    });

    function toggleTipoDocumento() {
        const tipo = $("#tipo_documento_id").val();

        if (tipo == 2) {
            $(".persona").hide();
            $(".empresa").show();

            $("#nombres").prop("required", false).val("");
            $("#apellidos").prop("required", false).val("");
            $("#fecha_nacimiento").prop("required", false).val("");

            $("#razon_social").prop("required", true);
        } else {
            $(".persona").show();
            $(".empresa").hide();

            $("#nombres").prop("required", true);
            $("#apellidos").prop("required", true);

            $("#razon_social").prop("required", false).val("");
        }
    }

    $("#tipo_documento_id").on("change", toggleTipoDocumento);

    let searchTimeout;
    $("#filtroTipoDocumento, #filtroDocumento, #filtroNombres").on(
        "keyup change",
        function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => tabla.ajax.reload(), 400);
        },
    );

    $("#correo").on("input", function () {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value === "") {
            this.setCustomValidity("");
            return;
        }
        this.setCustomValidity(regex.test(this.value) ? "" : "Correo inválido");
    });

    document
        .getElementById("filtroDocumento")
        .addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 11);
        });

    document
        .getElementById("filtroDocumento")
        .addEventListener("keypress", function (e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });

    document
        .getElementById("filtroNombres")
        .addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]/g, "");
        });

    function cargarListasCliente(callback = null) {
        $.get(route("listas.all"), function (res) {
            const fillSelect = (
                selector,
                items,
                placeholder,
                key = "descripcion",
            ) => {
                const select = $(selector);
                select
                    .empty()
                    .append(`<option value="">${placeholder}</option>`);
                items.forEach((i) =>
                    select.append(`<option value="${i.id}">${i[key]}</option>`),
                );
            };

            fillSelect("#cargo_id", res.cargos, "Seleccione un cargo");
            fillSelect(
                "#sucursal_id",
                res.sucursales,
                "Seleccione una sucursal",
                "nombre_comercial",
            );
            fillSelect(
                "#tipo_documento_id",
                res.tipos_documento,
                "Seleccione",
                "codigo",
            );
            fillSelect("#tipo_licencia_id", res.tipos_licencia, "Seleccione");

            $("#tipo_documento_id").val(1).trigger("change");

            if (callback) callback();
        }).fail(() => alert("Error al cargar las listas."));
    }

    $("#documento").on("input", function () {
        const tipo = $("#tipo_documento_id").val();
        let max = 20;

        if (tipo == 1) max = 8;
        if (tipo == 2) max = 11;
        if (tipo == 3) max = 9;

        this.value = this.value.replace(/\D/g, "").slice(0, max);
    });

    $("#tipo_documento_id").on("change", function () {
        $("#documento").val("");
    });

    $("#nombres, #apellidos").on("input", function () {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");
    });

    $("#celular, #telefono").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 9);
    });

    $("#btnNuevoCliente").click(() => {
        $("#formCliente")[0].reset();
        $("#cliente_id").val("");

        cargarSelectDepartamentos();
        $("#provincia_id")
            .empty()
            .append('<option value="">Seleccione</option>');
        $("#distrito_id")
            .empty()
            .append('<option value="">Seleccione</option>');

        cargarListasCliente(() => {
            habilitarModoEdicion();
            modal.show();
        });

        toggleTipoDocumento();
    });

    function cargarCliente(url, viewOnly = false) {
        cargarListasCliente(() => {
            $.get(url, (res) => {
                const p = res.persona ?? {};

                $("#cliente_id").val(res.id);
                $("#correo").val(p.correo ?? "");
                $("#telefono").val(p.telefono ?? "");
                $("#celular").val(p.celular ?? "");
                $("#direccion").val(p.direccion ?? "");
                $("#razon_social").val(p.razon_social ?? "");
                $("#nombres").val(p.nombres ?? "");
                $("#apellidos").val(p.apellidos ?? "");

                if (p.fecha_nacimiento) {
                    $("#fecha_nacimiento").val(
                        p.fecha_nacimiento.substring(0, 10),
                    );
                }

                $("#tipo_documento_id")
                    .val(p.tipo_documento_id ?? "")
                    .trigger("change");

                $("#documento").val(p.documento ?? "");

                setUbigeo(p.departamento_id, p.provincia_id, p.distrito_id);

                if (viewOnly) {
                    habilitarModoVista();
                } else {
                    habilitarModoEdicion();
                }

                modal.show();
                lucide.createIcons();
            });
        });
    }

    function habilitarModoVista() {
        $("#formCliente")
            .find("input, select, textarea, button")
            .not('[type="hidden"], .btn-close, #btnCerrarModal')
            .prop("disabled", true);

        $("#btnGuardar").addClass("d-none");
        $("#modalCliente .modal-title").html(
            '<i data-lucide="info"></i> Información del Cliente',
        );
    }

    function habilitarModoEdicion() {
        $("#formCliente")
            .find("input, select, textarea")
            .not('[type="hidden"]')
            .prop("disabled", false);

        $("#btnBuscarDocumento").prop("disabled", false);
        $("#btnGuardar").removeClass("d-none");
        $("#modalCliente .modal-title").html(
            '<i data-lucide="user"></i> Registrar / Editar Cliente',
        );
    }

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

    $(document).on("click", ".ver", function () {
        let id = $(this).data("id");
        let url = route("clientes.edit", id);
        cargarCliente(url, true);
    });

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id");
        if (parseInt(id) === 3) {
            Swal.fire(
                "Bloqueado",
                "El cliente con ID 3 no se puede modificar.",
                "warning",
            );
            return;
        }
        let url = route("clientes.edit", id);
        cargarCliente(url, false);
    });

    $(document).on("click", ".eliminar", function () {
        let id = $(this).data("id");

        if (parseInt(id) === 3) {
            Swal.fire(
                "Bloqueado",
                "El cliente con ID 3 no se puede eliminar.",
                "warning",
            );
            return;
        }

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Este cliente será eliminado permanentemente",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("clientes.destroy", id),
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            text: "El cliente fue eliminado correctamente",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        tabla.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire(
                            "Error",
                            xhr.responseJSON?.message ||
                                "No se pudo eliminar el cliente",
                            "error",
                        );
                    },
                });
            }
        });
    });

    $("#formCliente").on("submit", function (e) {
        e.preventDefault();

        const id = $("#cliente_id").val();

        if (parseInt(id) === 3) {
            Swal.fire(
                "Bloqueado",
                "El cliente con ID 3 no se puede modificar.",
                "warning",
            );
            return;
        }

        let url = id ? route("clientes.update", id) : route("clientes.store");
        let formData = $(this).serialize();
        if (id) formData += "&_method=PUT";

        const tipo = $("#tipo_documento_id").val();
        let max = 20;

        if (tipo == 1) max = 8;
        if (tipo == 2) max = 11;
        if (tipo == 3) max = 9;

        const valor = $("#documento").val();

        if (valor.length !== max) {
            Swal.fire({
                icon: "warning",
                title: "Validación",
                text: "El documento debe tener exactamente " + max + " dígitos",
            });
            return;
        }

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function () {
                Swal.fire({
                    icon: "success",
                    title: "Cliente registrado",
                    text: "El cliente fue guardado correctamente",
                    timer: 1500,
                    showConfirmButton: false,
                });

                $("#modalCliente").modal("hide");
                tabla.ajax.reload();
            },
            error: function (err) {
                let mensaje = "Ocurrió un error.";
                if (err.responseJSON?.message) {
                    mensaje = err.responseJSON.message;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: mensaje,
                });
            },
        });
    });

    $("#modalCliente").on("hidden.bs.modal", () => {
        habilitarModoEdicion();
    });
});
