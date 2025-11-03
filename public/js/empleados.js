$(document).ready(function () {
    // Inicializar DataTable
    const modal = new bootstrap.Modal($("#modalEmpleado")[0]);

    const tabla = $("#tablaEmpleados").DataTable({
        ajax: "/empleados/datatable",
        columns: [
            { data: "documento", title: "Documento" },
            { data: "nombre", title: "Nombre" },
            { data: "area", title: "Área" },
            { data: "sucursal", title: "Sucursal" },
            { data: "cargo", title: "Cargo" },
            {
                data: "acciones",
                title: "Acciones",
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

    initUbigeos(
        "#departamento_id",
        "#provincia_id",
        "#distrito_id",
        "#sucursal_id"
    );

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
            const tipoLicenciaSelect = $("#tipo_licencia_id");
            tipoLicenciaSelect
                .empty()
                .append('<option value="">Seleccione</option>');
            res.tipos_licencia.forEach((tipo_licencia) => {
                tipoLicenciaSelect.append(
                    `<option value="${tipo_licencia.id}">${tipo_licencia.descripcion}</option>`
                );
            });
            if (callback) callback();
        }).fail(() => {
            alert("Error al cargar las listas.");
        });
    }

    $("#btnBuscarDocumento").on("click", function () {
        const documento = $("#documento").val();

        if (!documento) {
            Swal.fire(
                "Atención",
                "Por favor ingrese un número de documento",
                "warning"
            );
            return;
        }

        $("#btnBuscarDocumento")
            .prop("disabled", true)
            .html('<i class="link-icon" data-lucide="search"></i>');
        lucide.createIcons();

        $.ajax({
            url: `/buscar/?documento=${documento}`,
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Respuesta API:", data);

                if (data.error) {
                    Swal.fire(
                        "Error",
                        "No se encontró información: " + data.error,
                        "error"
                    );
                    return;
                }

                if (data.razon_social) {
                    // Empresa
                    $('input[name="nombres"]').val(data.razon_social);
                    $('input[name="apellidos"]').val("");
                    $('input[name="nombre_comercial"]').val(
                        data.nombre_comercial || ""
                    );
                    $('input[name="direccion"]').val(data.direccion || "");
                } else if (data.nombres) {
                    // Persona natural
                    $('input[name="nombres"]').val(data.nombres || "");
                    $('input[name="apellidos"]').val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim()
                    );
                } else {
                    Swal.fire(
                        "Atención",
                        "No se encontraron datos para este documento.",
                        "info"
                    );
                }
            },
            error: function (xhr) {
                console.error("Error al consultar:", xhr);
                Swal.fire(
                    "Error",
                    "Error al consultar la API. Ver consola.",
                    "error"
                );
            },
            complete: function () {
                $("#btnBuscarDocumento")
                    .prop("disabled", false)
                    .html('<i class="link-icon" data-lucide="search"></i> ');
                lucide.createIcons();
            },
        });
    });

    $(document).ready(function () {
        const $cargo = $("#cargo_id");
        const $conductor = $(".conductor");

        $cargo.on("change", function () {
            const cargoVal = $(this).val();

            if (cargoVal == 16) {
                $conductor.removeAttr("hidden");
            } else {
                $conductor.attr("hidden", true);
                $("#tipo_licencia_id").val("");
                $("#licencia_conducir").val("");
            }
        });

        $cargo.trigger("change");
    });

    // ✅ Mostrar / ocultar sección de usuario
    $("#chkUsuario").on("change", function () {
        if ($(this).is(":checked")) {
            $("#seccionUsuario").removeAttr("hidden").slideDown(200);
        } else {
            $("#seccionUsuario").slideUp(200, function () {
                $(this).attr("hidden", true);
                $("#usuario, #password").val("");
            });
        }
    });

    $("#togglePassword").on("click", function () {
        const input = $("#password");
        const icon = $(this).find("i");

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.attr("data-lucide", "eye-off");
        } else {
            input.attr("type", "password");
            icon.attr("data-lucide", "eye");
        }

        lucide.createIcons();
    });

    $("#btnNuevoEmpleado").click(function () {
        $("#formEmpleado")[0].reset();
        $("#empleado_id").val("");
        $("#usuario").val("");
        $("#password").val("");
        $("#empleado_id").val("");
        $("#seccionUsuario").hide();
        $("#chkUsuario").prop("checked", false);
        $("#area_id, #cargo_id, #sucursal_id").val(null).trigger("change");

        cargarListasEmpleado(() => {
            cargarUbigeosConSucursales(
                "#departamento_id",
                "#provincia_id",
                "#distrito_id",
                "#sucursal_id"
            );
            modal.show();
        });
    });

    $("#tablaEmpleados").on("click", ".editar", function () {
        const id = $(this).data("id");

        cargarListasEmpleado(() => {
            $.get(`/empleados/${id}`, function (res) {
                $("#empleado_id").val(res.id);
                $("#documento").val(res.persona.documento);
                $("#nombres").val(res.persona.nombres);
                $("#apellidos").val(res.persona.apellidos);
                $("#correo").val(res.persona.correo);
                $("#telefono").val(res.persona.telefono);
                $("#direccion").val(res.persona.direccion);

                cargarUbigeosConSucursales(
                    "#departamento_id",
                    "#provincia_id",
                    "#distrito_id",
                    "#sucursal_id",
                    res.departamento_id,
                    res.provincia_id,
                    res.distrito_id,
                    res.sucursal_id
                );

                $("#area_id").val(res.area_id);
                $("#cargo_id").val(res.cargo_id);
                $("#tipo_licencia_id").val(res.tipo_licencia_id);
                modal.show();
            });
        });
    });
    // ✅ Envío del formulario por AJAX
    $("#formEmpleado").on("submit", function (e) {
        e.preventDefault(); // Evita el envío tradicional

        const form = $(this);
        const data = form.serialize();

        $.ajax({
            url: "/empleados/guardar",
            method: "POST",
            data: data,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    $("#modalEmpleado").modal("hide");
                    $("#tablaEmpleados").DataTable().ajax.reload(null, false); // Recargar sin perder la página
                } else {
                    Swal.fire("Atención", res.message, "warning");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire("Error", "No se pudo guardar el empleado.", "error");
            },
        });
    });

    // 🔍 VER EMPLEADO - Adaptado al JSON real
    $("#tablaEmpleados").on("click", ".ver", function () {
        const id = $(this).data("id");

        // Limpieza general
        $("#formEmpleado")[0].reset();
        $("#empleado_id").val("");

        cargarListasEmpleado(function () {
            $.get(`/empleados/${id}`, function (res) {
                const persona = res.persona ?? {};

                // 🧾 Datos personales
                $("#empleado_id").val(res.id ?? "");
                $("#tipo_documento_id").val(persona.tipo_documento_id ?? "");
                $("#documento").val(persona.documento ?? "");
                $("#nombres").val(persona.nombres ?? "");
                $("#apellidos").val(persona.apellidos ?? "");
                $("#fecha_nacimiento").val(persona.fecha_nacimiento ?? "");
                $("#correo").val(persona.correo ?? "");
                $("#telefono").val(persona.telefono ?? "");
                $("#celular").val(persona.celular ?? "");
                $("#direccion").val(persona.direccion ?? "");

                // 🌍 Ubicación: solo distrito
                const distritoId =
                    persona.distrito_id ?? persona.distrito?.id ?? null;
                if (distritoId)
                    $("#distrito_id").val(distritoId).trigger("change");

                // Ocultar provincia y departamento
                $(".campo-ubicacion").hide();

                // 🏢 Sucursal
                const sucursalId = res.sucursal_id ?? res.sucursal?.id ?? null;
                if (sucursalId)
                    $("#sucursal_id").val(sucursalId).trigger("change");

                // 🧠 Área y cargo
                $("#area_id").val(res.area_id ?? res.area?.id ?? "");
                $("#cargo_id").val(res.cargo_id ?? res.cargo?.id ?? "");
                $("#fecha_ingreso").val(res.fecha_ingreso ?? "");

                // 🚗 Si es conductor
                const cargoDesc = (res.cargo?.descripcion ?? "").toLowerCase();
                if (res.cargo_id === 16 || cargoDesc.includes("conductor")) {
                    $(".conductor").removeAttr("hidden").show();
                    $("#licencia_conducir").val(res.licencia_conducir ?? "");
                    $("#fecha_vencimiento_licencia").val(
                        res.fecha_vencimiento_licencia ?? ""
                    );
                    $("#tipo_licencia_id").val(res.tipo_licencia_id ?? "");
                } else {
                    $(".conductor").attr("hidden", true).hide();
                }

                // 👤 Usuario
                const usuarioValor =
                    res.usuario ??
                    res.usuario_nombre ??
                    res.user?.username ??
                    res.user?.name ??
                    res.persona?.usuario ??
                    "";

                if (usuarioValor) {
                    $("#chkUsuario").prop("checked", true);
                    $("#seccionUsuario").removeAttr("hidden").show();
                    $("#usuario").val(usuarioValor);
                } else {
                    $("#chkUsuario").prop("checked", false);
                    $("#seccionUsuario").hide().attr("hidden", true);
                    $("#usuario").val("");
                }

                // 🔒 Deshabilitar todos los campos
                $("#formEmpleado")
                    .find("input, select, textarea")
                    .not('[type="hidden"]')
                    .prop("disabled", true);

                // 🔘 Mostrar solo botón cerrar
                $("#btnGuardar").addClass("d-none");
                $("#btnCerrarModal").removeClass("d-none").show();

                // 🏷️ Cambiar título del modal
                $("#modalEmpleado .modal-title").html(
                    '<i data-lucide="eye"></i> Ver Empleado'
                );

                // Mostrar modal
                const modal = new bootstrap.Modal($("#modalEmpleado")[0]);
                modal.show();

                // Refrescar íconos
                if (typeof lucide !== "undefined") lucide.createIcons();
            }).fail(() => {
                Swal.fire(
                    "Error",
                    "No se pudieron cargar los datos del empleado.",
                    "error"
                );
            });
        });
    });

    $("#modalEmpleado").on("hidden.bs.modal", function () {
        // Rehabilitar los campos
        $("#formEmpleado")
            .find("input, select, textarea")
            .not('[type="hidden"]')
            .prop("disabled", false);

        // Mostrar botones por defecto
        $("#btnGuardar").removeClass("d-none");
        $("#btnCerrarModal").addClass("d-none").hide();

        // Mostrar nuevamente provincia y departamento
        $(".campo-ubicacion").show();

        // Ocultar secciones condicionales
        $("#seccionUsuario").hide().attr("hidden", true);
        $(".conductor").attr("hidden", true).hide();

        // Restaurar título
        $("#modalEmpleado .modal-title").html(
            '<i data-lucide="user"></i> Registrar / Editar Empleado'
        );
    });
});
