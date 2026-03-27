let UBIGEO = null;
let LISTAS = null;

$(document).ready(async function () {
    $("#buscarEmpleado").on("input", function () {
        const q = $(this).val().toLowerCase().trim();
        $(".emp-item").each(function () {
            const nombre = $(this).find(".emp-nombre").text().toLowerCase();
            const cargo = $(this).find(".emp-cargo").text().toLowerCase();
            $(this).toggle(nombre.includes(q) || cargo.includes(q));
        });
    });

    function renderEmpleados(data) {
        const container = document.getElementById("listaEmpleados");
        if (!container) {
            return;
        }
        if (!data || !data.length) {
            container.innerHTML = `<p style="padding:16px 12px;font-size:13px;color:var(--subtexto)">Sin empleados registrados.</p>`;
            return;
        }

        container.innerHTML = data
            .map((emp) => {
                const activo = emp.estado;
                const badgeClass = activo ? "badge-activo" : "badge-inactivo";
                const badgeText = activo ? "Activo" : "Inactivo";

                let fechaIngreso = "";
                if (emp.fecha_ingreso) {
                    const raw = emp.fecha_ingreso.toString().trim();
                    if (/^\d{4}-\d{2}-\d{2}/.test(raw)) {
                        const parts = raw.substring(0, 10).split("-");
                        fechaIngreso = `${parts[2]}/${parts[1]}`;
                    } else {
                        fechaIngreso = raw.substring(0, 5); // tomar solo DD/MM si ya viene formateado
                    }
                }

                return `
        <div class="emp-item" data-id="${emp.id}">
            <div class="emp-info">
                <div class="emp-nombre">${emp.nombre ?? ""}</div>
                <div class="emp-cargo">${emp.cargo ?? ""}</div>
            </div>
            <div class="emp-meta">
                <span class="emp-fecha">${fechaIngreso}</span>
                <span class="badge-estado ${badgeClass}">${badgeText}</span>
              
                <button class="btn btn-secondary btn-xs ver" data-id="${emp.id}">
                    <i class="link-icon " <i data-lucide="info"></i>

                </button>
                <button class="btn btn-warning btn-xs editar" data-id="${emp.id}">
                    <i class="link-icon" data-lucide="pen"></i>
                </button>
                <button class="btn btn-danger btn-xs eliminar" data-id="${emp.id}">
                    <i class="link-icon" data-lucide="trash-2"></i>
                </button>
        
            </div>
        </div>`;
            })
            .join("");

        lucide.createIcons();
    }

    await Promise.all([
        $.get(route("ubigeos.todo")),
        $.get(route("listas.all")),
    ]).then(([ubigeo, listas]) => {
        UBIGEO = ubigeo;
        LISTAS = listas;
    });

    initUbigeosEmpleado();
    poblarListasEmpleado();
    cargarListaEmpleados();

    const modal = new bootstrap.Modal($("#modalEmpleado")[0]);

    function cargarListaEmpleados() {
        $.get(route("empleados.datatable"), function (res) {
            if (typeof renderEmpleados === "function") {
                renderEmpleados(res.data);
            }
        });
    }

    function toggleConductor(cargoVal, cargoDesc = "") {
        const $conductor = $(".conductor");
        if (cargoVal == 16 || cargoDesc.toLowerCase().includes("conductor")) {
            $conductor.removeAttr("hidden").show();
            $(
                "#tipo_licencia_id, #licencia_conducir, #fecha_vencimiento_licencia",
            ).attr("required", "required");
        } else {
            $conductor.attr("hidden", true).hide();
            $(
                "#tipo_licencia_id, #licencia_conducir, #fecha_vencimiento_licencia",
            )
                .removeAttr("required")
                .val("");
        }
    }

    function esMayorDeEdad(fechaNacimiento) {
        if (!fechaNacimiento) return false;
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate()))
            edad--;
        return edad >= 18;
    }

    // ─── Poblar selects desde caché ───────────────────────────────────────────
    function poblarListasEmpleado() {
        const res = LISTAS;
        const fillSelect = (
            selector,
            items,
            placeholder,
            key = "descripcion",
        ) => {
            const select = $(selector);
            select.empty().append(`<option value="">${placeholder}</option>`);
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
        fillSelect("#tipo_licencia_id", res.tipos_licencia, "Seleccione");

        const tiposPermitidos = res.tipos_documento.filter((t) => t.id != 2);
        fillSelect(
            "#tipo_documento_id",
            tiposPermitidos,
            "Seleccione",
            "codigo",
        );
        $("#tipo_documento_id").val(1).trigger("change");
    }

    $("#telefono").on("input", function () {
        this.value = this.value.replace(/\D/g, "");
    });
    $("#celular").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 9);
    });
    $("#apellidos").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });
    $("#nombres").on("input", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });
    $("#licencia_conducir").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 8);
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
    $("#fecha_nacimiento").on("change", function () {
        $("#fecha_ingreso").attr("min", $(this).val());
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
                    $('input[name="nombres"]').val(data.razon_social);
                    $('input[name="apellidos"]').val("");
                    $('input[name="nombre_comercial"]').val(
                        data.nombre_comercial || "",
                    );
                    $('input[name="direccion"]').val(data.direccion || "");
                } else {
                    $('input[name="nombres"]').val(data.nombres || "");
                    $('input[name="apellidos"]').val(
                        `${data.apellido_paterno || ""} ${data.apellido_materno || ""}`.trim(),
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
        toggleConductor($(this).val(), $("#cargo_id option:selected").text());
    });

    $("#chkUsuario").on("change", function () {
        if ($(this).is(":checked")) {
            $("#seccionUsuario").removeAttr("hidden").slideDown(200);
            $("#usuario, #password, #rol").attr("required", "required");
        } else {
            $("#usuario, #password, #rol").removeAttr("required").val("");

            $("#seccionUsuario").slideUp(200, function () {
                $(this).attr("hidden", true);
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

    $("#btnNuevoEmpleado").click(() => {
        $("#formEmpleado")[0].reset();
        $("#empleado_id, #usuario, #password").val("");
        $("#seccionUsuario").hide();
        $("#chkUsuario").prop("checked", false);
        $(".conductor").attr("hidden", true).hide();
        poblarListasEmpleado();
        $("#cargo_id, #sucursal_id").val(null).trigger("change");
        modal.show();
    });

    function cargarEmpleado(id, viewOnly = false) {
        Swal.fire({
            title: "Cargando datos...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        poblarListasEmpleado();

        $.get(route("empleados.mostrar", id), (res) => {
            Swal.close();
            const persona = res.persona ?? {};
            const usuario = res.usuario ?? null;

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
            $("#tipo_licencia_id").val(res.tipo_licencia_id).trigger("change");
            $("#licencia_conducir")
                .val(res.licencia_conducir)
                .trigger("change");
            $("#fecha_vencimiento_licencia")
                .val(res.fecha_vencimiento_licencia)
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

            if (usuario) {
                $("#chkUsuario").prop("checked", true);
                $("#seccionUsuario").removeAttr("hidden").show();
                $("#usuario").val(usuario.username);
                $("#rol")
                    .val(usuario.rol ?? "")
                    .trigger("change");
            } else {
                $("#chkUsuario").prop("checked", false);
                $("#seccionUsuario").attr("hidden", true);
                $("#usuario").val("");
            }

            modal.show();
            lucide.createIcons();
        });
    }

    $(document).on("click", ".editar", function () {
        const id = $(this).data("id");
        cargarEmpleado(id, false);
    });

    $(document).on("click", ".ver", function () {
        const id = $(this).data("id");
        cargarEmpleado(id, true);
    });

    $(document).on("click", ".eliminar", function () {
        const id = $(this).data("id");
        Swal.fire({
            icon: "warning",
            title: "¿Eliminar empleado?",
            text: "¿Está seguro que quiere eliminar este empleado?",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("empleados.eliminar", id),
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        cargarListaEmpleados();
                        Swal.fire(
                            "Éxito",
                            "Empleado eliminado correctamente",
                            "success",
                        );
                    },
                });
            }
        });
    });

    function recargarCalendario() {
        if (typeof route !== "function") return;

        $.get(route("eventos.get"), function (eventos) {
            if (
                window.calendar &&
                typeof window.calendar.removeAllEvents === "function"
            ) {
                window.calendar.removeAllEvents();
                window.calendar.addEventSource(eventos);
            }

            window.eventosLaravel = eventos;

            if (
                $("#proximosCumple").length &&
                typeof renderProximos === "function"
            ) {
                renderProximos();
            }
        }).fail(() => {
            console.warn(
                "No se pudo cargar eventos (probablemente esta vista no usa calendario)",
            );
        });
    }

    function calcularEdad(fechaNacimiento, fechaReferencia) {
        let edad =
            fechaReferencia.getFullYear() - fechaNacimiento.getFullYear();
        const mes = fechaReferencia.getMonth() - fechaNacimiento.getMonth();

        if (
            mes < 0 ||
            (mes === 0 && fechaReferencia.getDate() < fechaNacimiento.getDate())
        ) {
            edad--;
        }

        return edad;
    }

    $("#formEmpleado").on("submit", function (e) {
        e.preventDefault();

        const fechaNacimiento = new Date($("#fecha_nacimiento").val());
        const fechaIngreso = new Date($("#fecha_ingreso").val());

        if (!esMayorDeEdad(fechaNacimiento))
            return Swal.fire({
                icon: "warning",
                title: "Edad no permitida",
                text: "El empleado debe ser mayor de 18 años.",
            });

        if (fechaIngreso < fechaNacimiento)
            return Swal.fire({
                icon: "warning",
                title: "Fecha inválida",
                text: "La fecha de ingreso no puede ser menor que la fecha de nacimiento.",
            });

        const edad = calcularEdad(fechaNacimiento, fechaIngreso);

        if (edad < 18)
            return Swal.fire({
                icon: "warning",
                title: "Fecha inválida",
                text: "El empleado debe tener al menos 18 años para trabajar, cambie la fecha de ingreso",
            });

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
                    cargarListaEmpleados();
                    recargarCalendario();
                    $(document).trigger("empleado:guardado");
                }
            })
            .fail((xhr) => {
                let mensaje = "Ocurrió un error";

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        mensaje = Object.values(xhr.responseJSON.errors)
                            .flat()
                            .join("\n");
                    } else if (xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: mensaje,
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
            const dep = UBIGEO.find((d) => d.id == this.value);
            $prov.empty().append('<option value="">Seleccione</option>');
            $dist.empty().append('<option value="">Seleccione</option>');
            dep?.provincias.forEach((p) => {
                $prov.append(`<option value="${p.id}">${p.nombre}</option>`);
            });
        });

        $prov.on("change", function () {
            const dep = UBIGEO.find((d) => d.id == $("#departamento_id").val());
            const prov = dep?.provincias.find((p) => p.id == this.value);
            $dist.empty().append('<option value="">Seleccione</option>');
            prov?.distritos.forEach((d) => {
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
