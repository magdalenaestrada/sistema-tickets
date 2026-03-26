let UBIGEO = null;

$(document).ready(async function () {
    UBIGEO = await $.get(route("ubigeos.todo"));
    cargarSelectDepartamentos();

    const modal = new bootstrap.Modal($("#modalCliente")[0]);

    const tabla = $("#tablaClientes").DataTable({
        processing: true,
        serverSide: true,
        dom: "rtip",
        ajax: {
            url: route("clientes.datatable"),
            data: function (d) {
                d.documento = $("#filtroDocumento").val();
                d.nombres = $("#filtroNombres").val();
                d.apellidos = $("#filtroApellidos").val();
            },
        },
        columns: [
            { data: "documento", title: "Documento" },
            {
                data: "nombre",
                title: "Razón social",
            },
            { data: "telefono", title: "Teléfono" },
            { data: "celular", title: "Celular" },
            { data: "correo", title: "Correo" },
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
            $("#nombres").prop("required", false);
            $("#apellidos").prop("required", false);
            $("#fecha_nacimiento").prop("required", false);

            $("#nombres").val("");
            $("#apellidos").val("");
            $("#fecha_nacimiento").val("");

            $("#razon_social").prop("required", true);
            $("#razon_social").prop("hidden", false);
        } else {
            $(".persona").show();
            $(".empresa").hide();
            $("#nombres").prop("required", true);
            $("#apellidos").prop("required", true);
            $("#razon_social").prop("required", false);
            $("#razon_social").val("");
        }
    }

    $("#tipo_documento_id").on("change", toggleTipoDocumento);

    $("#filtroDocumento, #filtroNombres, #filtroApellidos").on(
        "keyup change",
        function () {
            tabla.ajax.reload();
        },
    );

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
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
        });

    function toggleConductor(cargoVal, cargoDesc = "") {
        const $conductor = $(".conductor");
        if (cargoVal == 16 || cargoDesc.toLowerCase().includes("conductor")) {
            $conductor.removeAttr("hidden").show();
        } else {
            $conductor.attr("hidden", true).hide();
            $("#tipo_licencia_id, #licencia_conducir").val("");
        }
    }

    $(document).on("click", ".editar", function () {
        const id = $(this).data("id");

        $.get(route("clientes.edit", id), function (data) {
            $("#cliente_id").val(data.id);

            $("#documento").val(data.persona.documento);
            $("#nombres").val(data.persona.nombres);
            $("#apellidos").val(data.persona.apellidos);
            $("#razon_social").val(data.persona.razon_social);
            $("#telefono").val(data.persona.telefono);
            $("#celular").val(data.persona.celular);
            $("#correo").val(data.persona.correo);
            $("#direccion").val(data.persona.direccion);
            $("#fecha_nacimiento").val(data.persona.fecha_nacimiento);

            $("#modalCliente").modal("show");
        });
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

    $("#filtroEmpleado").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });

    $("#filtroDocumento").attr("maxlength", 11);

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

    $("#btnBuscarDocumento").on("click", function () {
        const documento = $("#documento").val().trim();
        if (!documento)
            return Swal.fire(
                "Atención",
                "Por favor ingrese un número de documento",
                "warning",
            );

        $("#btnBuscarDocumento")
            .prop("disabled", true)
            .html('<i data-lucide="search"></i>');
        lucide.createIcons();

        $.getJSON(route("buscar.buscar", { documento }))
            .done((data) => {
                if (data.error)
                    return Swal.fire(
                        "Error",
                        "No se encontró información: " + data.error,
                        "error",
                    );

                if (data.razon_social) {
                    $('input[name="razon_social"]').val(
                        data.razon_social || "",
                    );
                    $('input[name="direccion"]').val(data.direccion || "");
                } else {
                    $('input[name="nombres"]').val(data.nombres || "");
                    $('input[name="apellidos"]').val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim(),
                    );
                }
            })
            .fail(() =>
                Swal.fire(
                    "Error",
                    "Ingrese un numero de documento válido.",
                    "error",
                ),
            )
            .always(() => {
                $("#btnBuscarDocumento")
                    .prop("disabled", false)
                    .html('<i data-lucide="search"></i>');
                lucide.createIcons();
            });
    });

    $("#celular").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 9);
    });

    // Nuevo empleado
    $("#btnNuevoCliente").click(() => {
        $("#formCliente")[0].reset();
        $("#empleado_id, #usuario, #password").val("");
        $("#seccionUsuario").hide();
        $("#tipo_documento_id").val(1).trigger("change");
        $("#chkUsuario").prop("checked", false);
        $(".conductor").attr("hidden", true).hide();
        $("#cargo_id, #sucursal_id").val(null).trigger("change");

        cargarSelectDepartamentos();
        $("#provincia_id")
            .empty()
            .append('<option value="">Seleccione</option>');
        $("#distrito_id")
            .empty()
            .append('<option value="">Seleccione</option>');

        cargarListasCliente(() => modal.show());

        toggleTipoDocumento();
    });

    function cargarCliente(url, viewOnly = false) {
        cargarListasCliente(() => {
            $.get(url, (res) => {
                const p = res.persona ?? {};

                $("#empleado_id").val(res.id);
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

                modal.show();
                lucide.createIcons();
            });
        });
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

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id");
        let url = route("clientes.edit", id);

        cargarCliente(url);
    });
    $(document).on("click", ".eliminar", function () {
        let id = $(this).data("id");

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
                    error: function () {
                        Swal.fire(
                            "Error",
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
        let url = id ? route("clientes.update", id) : route("clientes.store");
        let method = id ? "PUT" : "POST";
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
        $("#formCliente")
            .find("input, select, textarea")
            .not('[type="hidden"]')
            .prop("disabled", false);
        $("#btnGuardar").removeClass("d-none");
        $("#btnCerrarModal").addClass("d-none").hide();
        $(".campo-ubicacion").show();
        $("#seccionUsuario").hide().attr("hidden", true);
        $(".conductor").attr("hidden", true).hide();
        $("#modalCliente .modal-title").html(
            '<i data-lucide="user"></i> Registrar / Editar Cliente',
        );
    });
});
