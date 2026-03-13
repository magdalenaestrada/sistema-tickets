let UBIGEO = null;

$(document).ready(async function () {
    UBIGEO = await $.get(route("ubigeos.todo"));
    initUbigeosEmpleado();
    const modal = new bootstrap.Modal($("#modalEmpleado")[0]);

    const tabla = $("#tablaEmpleados").DataTable({
        ajax: route("empleados.datatable"),
        columns: [
            { data: "documento", title: "Documento" },
            { data: "nombre", title: "Nombre" },
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
        searching: true,
        paging: false,
        info: false,
        dom: "rt",
        lengthChange: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: () => lucide.createIcons(),
    });

    $("#filtroDni").on("keyup change", function () {
        tabla.column(0).search(this.value).draw();
    });

    $("#filtroNombre").on("keyup change", function () {
        tabla.column(1).search(this.value).draw();
    });

    $("#filtroSucursal").on("keyup change", function () {
        tabla.column(2).search(this.value).draw();
    });

    $("#filtroCargo").on("keyup change", function () {
        tabla.column(3).search(this.value).draw();
    });

    $("#filtroDni").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 8);
    });

    function limpiarFiltros() {
        $("#filtroDni, #filtroSucursal, #filtroCargo, #filtroNombre").val("");
        tabla.columns().search("").draw();
    }

    function toggleConductor(cargoVal, cargoDesc = "") {
        const $conductor = $(".conductor");
        if (cargoVal == 16 || cargoDesc.toLowerCase().includes("conductor")) {
            $conductor.removeAttr("hidden").show();
        } else {
            $conductor.attr("hidden", true).hide();
            $("#tipo_licencia_id, #licencia_conducir").val("");
        }
    }

    $("#telefono").on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    $("#celular").on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    $("#apellidos").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });

    $("#nombres").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });

    function cargarListasEmpleado(callback = null) {
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
            const tiposPermitidos = res.tipos_documento.filter(
                (t) => t.id != 2,
            );

            fillSelect(
                "#tipo_documento_id",
                tiposPermitidos,
                "Seleccione",
                "codigo",
            );
            $("#tipo_documento_id").val(1).trigger("change");

            fillSelect("#tipo_licencia_id", res.tipos_licencia, "Seleccione");

            if (callback) callback();
        }).fail(() => alert("Error al cargar las listas."));
    }

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
                    $('input[name="nombres"]').val(data.razon_social);
                    $('input[name="apellidos"]').val("");
                    $('input[name="nombre_comercial"]').val(
                        data.nombre_comercial || "",
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

    $("#cargo_id").on("change", function () {
        const cargoVal = $(this).val();
        const cargoDesc = $("#cargo_id option:selected").text();
        toggleConductor(cargoVal, cargoDesc);
    });

    $("#chkUsuario").on("change", function () {
        if ($(this).is(":checked"))
            $("#seccionUsuario").removeAttr("hidden").slideDown(200);
        else
            $("#seccionUsuario").slideUp(200, () => {
                $(this).attr("hidden", true);
                $("#usuario, #password").val("");
            });
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

    $("#documento").on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    $("#tipo_documento_id").on("change", function () {
        $("#documento").val("");
    });

    $("#documento").on("input", function () {
        const tipo = $("#tipo_documento_id").val();
        let max = 20;

        if (tipo == 1) max = 8;
        if (tipo == 3) max = 9;

        this.value = this.value.replace(/\D/g, "").slice(0, max);
    });

    $("#celular").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 9);
    });

    // Nuevo empleado
    $("#btnNuevoEmpleado").click(() => {
        $("#formEmpleado")[0].reset();
        $("#empleado_id, #usuario, #password").val("");
        $("#seccionUsuario").hide();
        $("#chkUsuario").prop("checked", false);
        $(".conductor").attr("hidden", true).hide();
        $("#cargo_id, #sucursal_id").val(null).trigger("change");

        cargarListasEmpleado(() => modal.show());
    });

    // Editar y ver empleado
    function cargarEmpleado(id, viewOnly = false) {
        cargarListasEmpleado(() => {
            $.get(route("empleados.mostrar", id), (res) => {
                const persona = res.persona ?? {};
                $("#empleado_id").val(res.id);
                $("#nombres").val(persona.nombres ?? "");
                $("#apellidos").val(persona.apellidos ?? "");
                $("#correo").val(persona.correo ?? "");
                $("#telefono").val(persona.telefono ?? "");
                $("#celular").val(persona.celular ?? "");
                $("#direccion").val(persona.direccion ?? "");
                $("#fecha_nacimiento").val(persona.fecha_nacimiento ?? "");
                $("#tipo_documento_id")
                    .val(persona.tipo_documento_id ?? "")
                    .trigger("change");
                $("#documento").val(persona.documento ?? "");

                $("#fecha_ingreso").val(
                    res.fecha_ingreso ? res.fecha_ingreso.substring(0, 10) : "",
                );

                if (persona.distrito) {
                    cargarUbicacionPorIds(
                        persona.distrito.provincia.departamento.id,
                        persona.distrito.provincia.id,
                        persona.distrito.id,
                    );
                }

                $("#sucursal_id").val(res.sucursal_id).trigger("change");
                $("#cargo_id").val(res.cargo_id).trigger("change");
                $("#tipo_licencia_id")
                    .val(res.tipo_licencia_id)
                    .trigger("change");

                toggleConductor(res.cargo_id, res.cargo?.descripcion ?? "");
                if (viewOnly) {
                    $("#formEmpleado")
                        .find("input, select, textarea")
                        .not('[type="hidden"]')
                        .prop("disabled", true);
                    $("#btnGuardar").addClass("d-none");
                    $("#btnCerrarModal").removeClass("d-none").show();
                    $("#modalEmpleado .modal-title").html(
                        '<i data-lucide="info"></i> Ver Empleado',
                    );
                } else {
                    $("#formEmpleado")
                        .find("input, select, textarea")
                        .prop("disabled", false);
                    $("#btnGuardar").removeClass("d-none");
                    $("#btnCerrarModal").addClass("d-none").hide();
                }
                modal.show();
                lucide.createIcons();
            });
        });
    }

    $("#tablaEmpleados").on("click", ".editar", function () {
        cargarEmpleado($(this).data("id"));
    });
    $("#tablaEmpleados").on("click", ".ver", function () {
        cargarEmpleado($(this).data("id"), true);
    });

    function esMayorDeEdad(fechaNacimiento) {
        if (!fechaNacimiento) return false;

        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);

        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }

        return edad >= 18;
    }

    $("#licencia_conducir").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 8);
    });

    $("#formEmpleado").on("submit", function (e) {
        e.preventDefault();
        const fechaNacimiento = $("#fecha_nacimiento").val();
        if (!esMayorDeEdad(fechaNacimiento)) {
            Swal.fire({
                icon: "warning",
                title: "Edad no permitida",
                text: "El empleado debe ser mayor de 18 años.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        $.post(route("empleados.guardar"), $(this).serialize())
            .done((res) => {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    $("#modalEmpleado").modal("hide");
                    tabla.ajax.reload(null, false);
                    $(document).trigger("empleado:guardado");
                }
            })
            .fail((xhr) => {
                let mensaje = "Error al guardar el empleado";

                if (xhr.status === 422 && xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                } else if (xhr.status === 500 && xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    mensaje = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join("<br>");
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: mensaje,
                    confirmButtonText: "Entendido",
                    confirmButtonColor: "#d33",
                });
            });
    });

    $("#modalEmpleado").on("hidden.bs.modal", () => {
        $("#formEmpleado")
            .find("input, select, textarea")
            .not('[type="hidden"]')
            .prop("disabled", false);
        $("#btnGuardar").removeClass("d-none");
        $("#btnCerrarModal").addClass("d-none").hide();
        $(".campo-ubicacion").show();
        $("#seccionUsuario").hide().attr("hidden", true);
        $(".conductor").attr("hidden", true).hide();
        $("#modalEmpleado .modal-title").html(
            '<i data-lucide="user"></i> Registrar / Editar Empleado',
        );
    });

    function initUbigeosEmpleado() {
        const $dep = $("#departamento_id");
        const $prov = $("#provincia_id");
        const $dist = $("#distrito_id");

        $dep.empty().append('<option value="">Seleccione</option>');
        UBIGEO.forEach((dep) => {
            $dep.append(`<option value="${dep.id}">${dep.nombre}</option>`);
        });

        $dep.on("change", function () {
            const depId = this.value;
            $prov.empty().append('<option value="">Seleccione</option>');
            $dist.empty().append('<option value="">Seleccione</option>');

            const dep = UBIGEO.find((d) => d.id == depId);
            if (!dep) return;

            dep.provincias.forEach((p) => {
                $prov.append(`<option value="${p.id}">${p.nombre}</option>`);
            });
        });

        $prov.on("change", function () {
            const depId = $dep.val();
            const provId = this.value;
            $dist.empty().append('<option value="">Seleccione</option>');

            const dep = UBIGEO.find((d) => d.id == depId);
            const prov = dep?.provincias.find((p) => p.id == provId);
            if (!prov) return;

            prov.distritos.forEach((d) => {
                $dist.append(`<option value="${d.id}">${d.nombre}</option>`);
            });
        });
    }

    function cargarUbicacionPorIds(depId, provId, distId) {
        $("#departamento_id").val(depId).trigger("change");
        $("#provincia_id").val(provId).trigger("change");
        $("#distrito_id").val(distId);
    }
});
