$(document).ready(function () {
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

    initUbigeos("#departamento_id", "#provincia_id", "#distrito_id");

    $("#filtroDni").on("keyup change", function () {
        tabla.column(0).search(this.value).draw();
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
        $("#filtroDni, #filtroSucursal, #filtroCargo").val("");
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

    // Cargar listas de selects
    function cargarListasEmpleado(callback = null) {
        $.get(route("listas.all"), function (res) {
            const fillSelect = (
                selector,
                items,
                placeholder,
                key = "descripcion"
            ) => {
                const select = $(selector);
                select
                    .empty()
                    .append(`<option value="">${placeholder}</option>`);
                items.forEach((i) =>
                    select.append(`<option value="${i.id}">${i[key]}</option>`)
                );
            };

            fillSelect("#cargo_id", res.cargos, "Seleccione un cargo");
            fillSelect(
                "#sucursal_id",
                res.sucursales,
                "Seleccione una sucursal",
                "nombre_comercial"
            );
            const tiposPermitidos = res.tipos_documento.filter(
                (t) => t.id != 2
            );

            fillSelect(
                "#tipo_documento_id",
                tiposPermitidos,
                "Seleccione",
                "codigo"
            );

            fillSelect("#tipo_licencia_id", res.tipos_licencia, "Seleccione");

            if (callback) callback();
        }).fail(() => alert("Error al cargar las listas."));
    }

    // Buscar documento
    $("#btnBuscarDocumento").on("click", function () {
        const documento = $("#documento").val().trim();
        if (!documento)
            return Swal.fire(
                "Atención",
                "Por favor ingrese un número de documento",
                "warning"
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
                        "error"
                    );

                if (data.razon_social) {
                    $('input[name="nombres"]').val(data.razon_social);
                    $('input[name="apellidos"]').val("");
                    $('input[name="nombre_comercial"]').val(
                        data.nombre_comercial || ""
                    );
                    $('input[name="direccion"]').val(data.direccion || "");
                } else {
                    $('input[name="nombres"]').val(data.nombres || "");
                    $('input[name="apellidos"]').val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim()
                    );
                }
            })
            .fail(() =>
                Swal.fire("Error", "Error al consultar la API.", "error")
            )
            .always(() => {
                $("#btnBuscarDocumento")
                    .prop("disabled", false)
                    .html('<i data-lucide="search"></i>');
                lucide.createIcons();
            });
    });

    // Mostrar/ocultar conductor según cargo
    $("#cargo_id").on("change", function () {
        const cargoVal = $(this).val();
        const cargoDesc = $("#cargo_id option:selected").text();
        toggleConductor(cargoVal, cargoDesc);
    });

    // Mostrar/ocultar sección usuario
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
                    res.fecha_ingreso ? res.fecha_ingreso.substring(0, 10) : ""
                );

                cargarUbicacionPorIds(
                    persona.departamento_id ??
                        persona.distrito?.provincia?.departamento_id ??
                        null,
                    persona.provincia_id ??
                        persona.distrito?.provincia_id ??
                        null,
                    persona.distrito_id ?? null
                );

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
                        '<i data-lucide="info"></i> Ver Empleado'
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
                } else Swal.fire("Atención", res.message, "warning");
            })
            .fail(() =>
                Swal.fire("Error", "No se pudo guardar el empleado.", "error")
            );
    });

    // Reset modal
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
            '<i data-lucide="user"></i> Registrar / Editar Empleado'
        );
    });
});

// Funciones de Ubigeos
function initUbigeos(depSelectId, provSelectId, distSelectId) {
    /* tu lógica actual */
}
function cargarUbicacionPorIds(
    departamentoId,
    provinciaId,
    distritoId,
    sucursalId
) {
    /* tu lógica actual */
}
