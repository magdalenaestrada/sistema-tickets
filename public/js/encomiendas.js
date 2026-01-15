let UBIGEO = null;

function initUbigeosReceptor() {
    const $dep = $("#departamento_id");
    const $prov = $("#provincia_id");
    const $dist = $("#distrito_id");
    const $ubigeo = $("#receptor_ubigeo");

    $dep.empty().append('<option value="">Seleccione</option>');

    UBIGEO.forEach((d) => {
        $dep.append(`<option value="${d.id}">${d.nombre}</option>`);
    });

    $dep.on("change", function () {
        const depId = this.value;

        $prov.empty().append('<option value="">Seleccione</option>');
        $dist.empty().append('<option value="">Seleccione</option>');
        $ubigeo.val("");

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
        $ubigeo.val("");

        const dep = UBIGEO.find((d) => d.id == depId);
        const prov = dep?.provincias.find((p) => p.id == provId);
        if (!prov) return;

        prov.distritos.forEach((d) => {
            $dist.append(
                `<option value="${d.id}" data-ubigeo="${d.ubigeo}">
                    ${d.nombre}
                </option>`
            );
        });
    });

    $dist.on("change", function () {
        const ubigeo = $("#distrito_id option:selected").data("ubigeo");
        $ubigeo.val(ubigeo || "");
    });

    $("#distrito_id").trigger("change");;
}

$(function () {
    filtrarOrigenDestino();
    actualizarResumen();
    recalcularTotal();

    $(document).on("input", ".peso, .costo", recalcularTotal);

    $("#formEditarEncomienda").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: route("encomiendas.actualizar", $("#encomienda_id").val()),
            method: "POST",
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire("Actualizado", res.message, "success");
                }
            },
        });
    });

    function filtrarOrigenDestino() {
        const origen = $("#origen").val();
        const destino = $("#destino").val();
        $("#origen option, #destino option").show();
        if (origen) {
            $("#destino option[value='" + origen + "']").hide();
        }
        if (destino) {
            $("#origen option[value='" + destino + "']").hide();
        }
        if (origen && destino && origen === destino) {
            $("#destino").val("");
        }
    }

    $("#origen").on("change", function () {
        filtrarOrigenDestino();
        actualizarResumen();
    });

    $("#destino").on("change", function () {
        filtrarOrigenDestino();
        actualizarResumen();
    });

    let tabla = $("#tablaEncomiendas").DataTable({
        ajax: route("encomiendas.datatable.no-asignadas"),

        columns: [
            { data: "checkbox", orderable: false, searchable: false },
            { data: "id" },
            { data: "emisor" },
            { data: "dni_emisor" },
            { data: "receptor" },
            { data: "origen" },
            { data: "destino" },
            { data: "total" },
            { data: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        order: [[1, "desc"]],
        scrollX: true,
        lengthChange: false,
        searching: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
            actualizarContador();
        },
    });

    $("#filtroOrigen").on("change", function () {
        tabla.column(5).search($(this).val()).draw();
    });

    $("#filtroDestino").on("change", function () {
        tabla.column(6).search($(this).val()).draw();
    });

    const btnNueva = document.getElementById("btnNueva");

    if (btnNueva) {
        btnNueva.addEventListener("click", function () {
            fetch(route("caja.verificar"))
                .then((res) => res.json())
                .then((data) => {
                    if (!data.abierta) {
                        Swal.fire({
                            icon: "warning",
                            title: "Caja no abierta",
                            text: "Aún no has abierto caja. No puedes crear encomiendas.",
                            confirmButtonText: "Entendido",
                        });
                        return;
                    }
                    window.location.href = route(
                        "encomiendas.crear-encomienda"
                    );
                });
        });
    }

    $("#formEncomienda").submit(function (e) {
        e.preventDefault();
        if ($("#tablaDetalles tbody tr").length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No hay detalles",
                text: "Debes agregar al menos un detalle antes de guardar la encomienda.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        let detalleInvalido = false;

        $("#tablaDetalles tbody tr").each(function () {
            let tipo = $(this).find(".tipo").val();
            if (!tipo) detalleInvalido = true;
        });

        if (detalleInvalido) {
            Swal.fire({
                icon: "warning",
                title: "Detalle incompleto",
                text: "Todos los detalles deben tener un tipo seleccionado.",
                confirmButtonText: "Corregir",
            });
            return;
        }

        let detalles = [];
        $("#tablaDetalles tbody tr").each(function () {
            detalles.push({
                tipo_encomienda_id: $(this).find(".tipo").val(),
                tipo_encomienda_nombre: $(this)
                    .find(".tipo option:selected")
                    .text(),
                peso: $(this).find(".peso").val(),
                costo: $(this).find(".costo").val(),
                descripcion: $(this).find(".desc").val() || "Sin descripción",
            });
        });

        let pagos = [];
        let metodo = parseInt($("#metodo_pago_id").val());
        let total = parseFloat($("#costo_total").val()) || 0;

        if (metodo === 1) {
            pagos.push({ metodo_pago_id: 1, total: total });
        } else if (metodo === 2) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: $("#billetera_id").val(),
                total: total,
            });
        } else if (metodo === 3) {
            pagos.push({
                metodo_pago_id: 1,
                total: parseFloat($("#pago_efectivo").val()) || 0,
            });
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: $("#billetera_id").val(),
                total: parseFloat($("#pago_billetera").val()) || 0,
            });
        }

        let data = {
            _token: $("input[name=_token]").val(),
            emisor: {
                documento: $("#emisor_documento").val(),
                tipo_documento_id: $("#emisor_tipo_documento_id").val(),
                nombres: $("#emisor_nombres").val(),
                apellidos: $("#emisor_apellidos").val(),
                celular: $("#emisor_celular").val(),
                telefono: $("#emisor_telefono").val(),
                direccion: $("#emisor_direccion").val(),
            },
            receptor: {
                documento: $("#receptor_documento").val(),
                tipo_documento_id: $("#receptor_tipo_documento_id").val(),
                nombres: $("#receptor_nombres").val(),
                apellidos: $("#receptor_apellidos").val(),
                celular: $("#receptor_celular").val(),
                telefono: $("#receptor_telefono").val(),
                direccion: $("#receptor_direccion").val(),
            },
            origen: $("#origen").val(),
            distrito_id: $("#distrito_id").val(),
            destino: $("#destino").val(),
            tipo_documento_factura_id: $("#tipo_documento_factura_id").val(),
            total: total,
            detalles: detalles,
            tipo_servicio_id: 2,
            sucursal_id: null,
            serie: null,
            numero: null,
            pagos: pagos,
        };

        $.ajax({
            url: route("encomiendas.guardar"),
            method: "POST",
            data,
            success: function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Encomienda creada",
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1200);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message,
                    });
                }
            },
        });
    });

    let tiposEncomienda = [];

    $.get(route("tipo-encomienda.listar-todos"), function (res) {
        tiposEncomienda = res;
        agregarFilaDetalle();
    });

    $("#btnAgregarDetalle").click(() => agregarFilaDetalle());

    function agregarFilaDetalle() {
        let fila = $("<tr>");
        let tipoSelect = $('<select class="form-select tipo"></select>');
        tipoSelect.append(
            '<option value="" disabled selected>Selecciona un tipo</option>'
        );

        tiposEncomienda.forEach((t) => {
            tipoSelect.append(
                `<option value="${t.id}" data-precio="${t.precio_base}" data-peso-limite="${t.peso_limite}" data-costo-extra="${t.costo_kilo_extra}">${t.descripcion}</option>`
            );
        });

        fila.append($("<td>").append(tipoSelect));
        fila.append(
            $("<td>").append('<input type="text" class="form-control desc">')
        );
        fila.append(
            $("<td>").append(
                '<input type="number" class="form-control peso" step="0.01">'
            )
        );
        fila.append(
            $("<td>").append(
                '<input type="number" class="form-control costo" step="0.01">'
            )
        );
        fila.append(
            $("<td>").append(
                '<button type="button" class="btn btn-danger btn-sm btnQuitar">Eliminar</button>'
            )
        );

        $("#tablaDetalles tbody").append(fila);

        actualizarResumen();
        recalcularTotal();
    }

    $("#emisor_documento").on("blur", function () {
        let doc = $(this).val();
        if (!doc) return;

        $.get(route("buscar.buscar") + `?documento=${doc}`, function (res) {
            if (res.error) return alert(res.error);

            if (res.tipo === "DNI") {
                $("#emisor_nombres").val(res.nombres);
                $("#emisor_apellidos").val(
                    res.apellido_paterno + " " + res.apellido_materno
                );
            }

            if (res.tipo === "RUC") {
                $("#emisor_nombres").val(res.razon_social);
                $("#emisor_apellidos").val("");
                $("#emisor_direccion").val(res.direccion || "");
            }

            $("#numero_documento_id").val($("#emisor_documento").val());
            $("#razon_social").val(
                $("#emisor_nombres").val() + " " + $("#emisor_apellidos").val()
            );
        }).fail(function (err) {
            alert(err.responseJSON?.error || "Error al buscar documento");
        });
    });

    function recalcularTotal() {
        let total = 0;

        $("#tablaDetalles tbody tr").each(function () {
            total += parseFloat($(this).find(".costo").val()) || 0;
        });

        $("#costo_total").val(total.toFixed(2));

        let metodo = parseInt($("#metodo_pago_id").val());

        if (metodo === 1) {
            $("#pago_efectivo").val(total.toFixed(2));
            $("#pago_billetera").val(0);
        } else if (metodo === 2) {
            $("#pago_billetera").val(total.toFixed(2));
            $("#pago_efectivo").val(0);
        } else if (metodo === 3) {
            let pagoE = parseFloat($("#pago_efectivo").val()) || 0;

            if (pagoE > total) pagoE = total;
            $("#pago_efectivo").val(pagoE.toFixed(2));
            $("#pago_billetera").val((total - pagoE).toFixed(2));
        }
    }

    function actualizarResumen() {
        let totalPeso = 0;
        let totalBultos = 0;

        $("#tablaDetalles tbody tr").each(function () {
            let peso = parseFloat($(this).find(".peso").val()) || 0;
            if (peso > 0) totalBultos++;
            totalPeso += peso;
        });

        $("#peso_total").val(totalPeso.toFixed(2));
        $("#cantidad_bultos").val(totalBultos);
    }

    $(document).on("input", ".peso, .costo", function () {
        recalcularTotal();
        actualizarResumen();
    });

    $("#pago_efectivo").on("input", function () {
        if ($("#metodo_pago_id").val() != "3") return;

        let total = parseFloat($("#costo_total").val()) || 0;
        let efectivo = parseFloat($(this).val()) || 0;

        if (efectivo > total) efectivo = total;

        $("#pago_billetera").val((total - efectivo).toFixed(2));
    });

    $("#pago_billetera").on("input", function () {
        if ($("#metodo_pago_id").val() != "3") return;

        let total = parseFloat($("#costo_total").val()) || 0;
        let digital = parseFloat($(this).val()) || 0;

        if (digital > total) digital = total;

        $("#pago_efectivo").val((total - digital).toFixed(2));
    });

    $("#metodo_pago_id").on("change", function () {
        refrescarPagos();
    });

    refrescarPagos();

    $(document).on("change", ".tipo", function () {
        let tr = $(this).closest("tr");
        let tipoId = $(this).val();
        let tipo = tiposEncomienda.find((t) => t.id == tipoId);
        if (!tipo) return;

        let pesoInput = tr.find(".peso");

        if (!pesoInput.val()) {
            pesoInput.val(tipo.peso_limite || 1);
        }

        calcularCostoFila(tr, tipo);
    });

    $(document).on("input", ".peso", function () {
        let tr = $(this).closest("tr");
        let tipoId = tr.find(".tipo").val();
        let tipo = tiposEncomienda.find((t) => t.id == tipoId);
        if (!tipo) return;

        calcularCostoFila(tr, tipo);
    });

    function calcularCostoFila(tr, tipo) {
        let peso = parseFloat(tr.find(".peso").val()) || 0;

        let precioBase = parseFloat(tipo.precio_base) || 0;
        let costoKiloExtra = parseFloat(tipo.costo_kilo_extra) || 0;
        let pesoLimite = parseFloat(tipo.peso_limite) || 0;

        let costo = precioBase;

        if (pesoLimite && peso > pesoLimite && costoKiloExtra) {
            costo += (peso - pesoLimite) * costoKiloExtra;
        }

        tr.find(".costo").val(costo.toFixed(2));

        recalcularTotal();
        actualizarResumen();

        $(document).ready(function () {
            $("#metodo_pago_id").trigger("change");
        });
    }

    $(document).on("click", ".btnQuitar", function () {
        $(this).closest("tr").remove();
        actualizarResumen();
        recalcularTotal();
    });

    $(document).on(
        "input change",
        ".peso, #origen, #destino",
        actualizarResumen
    );
    $(document).on("change", ".tipo", actualizarResumen);

    $("#tipo_documento_id").on("change", function () {
        let tipo = $(this).val();

        if (tipo !== "1") {
            $("#numero_documento_id").val("");
            $("#razon_social").val("");
            $("#numero_serie").val("");
        } else {
            $("#razon_social").val(
                $("#emisor_nombres").val() + " " + $("#emisor_apellidos").val()
            );
        }
    });

    function debounce(fn, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function buscarPersona(tipo, campoDocumento = null) {
        let doc = campoDocumento
            ? $(campoDocumento).val()
            : $(`#${tipo}_documento`).val();
        if (!doc) return;

        $.get(route("buscar.buscar") + `?documento=${doc}`, function (res) {
            if (res.error) {
                alert(res.error);
                return;
            }

            if (res.tipo === "DNI") {
                $(`#${tipo}_nombres`).val(res.nombres);
                $(`#${tipo}_apellidos`).val(
                    res.apellido_paterno + " " + res.apellido_materno
                );
            } else if (res.tipo === "RUC") {
                $(`#${tipo}_nombres`).val(res.razon_social);
                $(`#${tipo}_apellidos`).val("");
                $(`#${tipo}_direccion`).val(res.direccion || "");
            }

            if (tipo === "emisor") updateRazonSocial();

            if (campoDocumento) {
                $("#numero_documento_id").val(doc);
            }
        }).fail(function (err) {
            alert(err.responseJSON?.error || "Error al buscar documento");
        });
    }

    function updateRazonSocial() {
        let tipo = $("#tipo_documento_id").val();
        if (tipo == "1") {
            $("#razon_social").val(
                $("#emisor_nombres").val() + " " + $("#emisor_apellidos").val()
            );
        }
    }

    $("#emisor_documento").on(
        "blur",
        debounce(() => buscarPersona("emisor"), 300)
    );
    $("#receptor_documento").on(
        "blur",
        debounce(() => buscarPersona("receptor"), 300)
    );
    $("#numero_documento_id").on(
        "blur",
        debounce(() => buscarPersona("emisor", "#numero_documento_id"), 300)
    );

    $("#tipo_documento_id").on("change", updateRazonSocial);

    $("#numero_documento_id").on("blur", function () {
        let numero = $(this).val();
        if (!numero) return;

        $.get(route("buscar.buscar") + `?documento=${numero}`, function (res) {
            if (res.error) {
                alert(res.error);
                return;
            }
            if (res.tipo === "DNI" || res.tipo === "RUC") {
                $("#razon_social").val(
                    res.razon_social ||
                        res.nombres +
                            " " +
                            res.apellido_paterno +
                            " " +
                            res.apellido_materno
                );
            }
        }).fail(function (err) {
            alert(err.responseJSON?.error || "Error al buscar documento");
        });
    });

    function calcularTotalPago() {
        let total = parseFloat($("#total").val()) || 0;
        let efectivo = parseFloat($("#pago_efectivo").val()) || 0;
        let billetera = parseFloat($("#pago_billetera").val()) || 0;

        let suma = efectivo + billetera;

        if (suma > total) suma = total;

        $("#costo_total").val(total.toFixed(2));
    }
    function refrescarPagos() {
        let metodo = parseInt($("#metodo_pago_id").val());
        let total = parseFloat($("#costo_total").val()) || 0;

        $("#pago_efectivo").closest(".row").hide();
        $("#billetera_id").closest(".row").hide();
        $("#pago_billetera").closest(".row").hide();

        $("#pago_efectivo").prop("readonly", false);
        $("#pago_billetera").prop("readonly", false);

        if (metodo === 1) {
            $("#pago_efectivo").closest(".row").show();
            $("#pago_efectivo").val(total.toFixed(2));
            $("#pago_efectivo").prop("readonly", true);
            $(".grupo_costo_total").attr("hidden", true);
        } else if (metodo === 2) {
            $("#billetera_id").closest(".row").show();
            $("#pago_billetera").closest(".row").show();
            $("#pago_billetera").val(total.toFixed(2));
            $("#pago_billetera").prop("readonly", true);
            $(".grupo_costo_total").attr("hidden", true);
        } else if (metodo === 3) {
            $("#pago_efectivo").closest(".row").show();
            $("#billetera_id").closest(".row").show();
            $("#pago_billetera").closest(".row").show();
            $(".grupo_costo_total").removeAttr("hidden");

            let pagoE = parseFloat($("#pago_efectivo").val()) || 0;
            if (pagoE > total) pagoE = total;

            $("#pago_efectivo").val(pagoE.toFixed(2));
            $("#pago_billetera").val((total - pagoE).toFixed(2));
        }
    }

    $("#metodo_pago_id").on("change", refrescarPagos);

    $(document).on("input", ".costo", function () {
        recalcularTotal();
        refrescarPagos();
    });

    refrescarPagos();

    // Seleccionar/Deseleccionar todos
    $("#checkAll").on("change", function () {
        $(".check-encomienda").prop("checked", $(this).prop("checked"));
        actualizarContador();
    });

    // Actualizar contador al seleccionar individual
    $(document).on("change", ".check-encomienda", function () {
        actualizarContador();

        let total = $(".check-encomienda").length;
        let seleccionados = $(".check-encomienda:checked").length;
        $("#checkAll").prop("checked", total === seleccionados && total > 0);
    });

    function actualizarContador() {
        let count = $(".check-encomienda:checked").length;
        $("#contadorSeleccionados").text(
            count + " seleccionada" + (count !== 1 ? "s" : "")
        );
    }

    $("#btnAsignar").on("click", function () {
        let asignacionId = $("#asignacion_id").val();

        if (!asignacionId) {
            alert("Debe seleccionar un horario/asignación");
            return;
        }

        let encomiendas = [];
        $(".check-encomienda:checked").each(function () {
            encomiendas.push($(this).val());
        });

        if (encomiendas.length === 0) {
            alert("Debe seleccionar al menos una encomienda");
            return;
        }

        if (
            !confirm(
                `¿Asignar ${encomiendas.length} encomienda(s) a esta asignación?`
            )
        ) {
            return;
        }

        $.ajax({
            url: route("encomiendas-asignacion.guardar"),
            method: "POST",
            data: {
                _token: csrf_token,
                asignacion_id: asignacionId,
                encomiendas: encomiendas,
            },
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    tabla.ajax.reload();
                    $("#checkAll").prop("checked", false);
                    $("#asignacion_id").val("");
                }
            },
            error: function (err) {
                alert(
                    err.responseJSON?.message || "Error al asignar encomiendas"
                );
            },
        });
    });

    $(document).on("input", ".solo-letras", function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
    });

    $(document).on("input", ".solo-numeros", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    $("#filtroDNI").on("keyup", function () {
        tabla.column(3).search(this.value).draw();
    });

    $("#filtroOrigen").on("change", function () {
        tabla.column(5).search(this.value).draw();
    });

    $("#filtroDestino").on("change", function () {
        tabla.column(6).search(this.value).draw();
    });

    $(document).on("click", ".imprimir", function () {
        let id = $(this).data("id");
        let url = route("encomiendas.ticket", id);
        let ventana = window.open(url, "_blank", "width=420,height=650");

        let timer = setInterval(function () {
            if (ventana.document.readyState === "complete") {
                ventana.print();
                clearInterval(timer);
            }
        }, 200);
    });

    $(document).on("click", ".editar", function () {
        let id = $(this).data("id");
        window.location.href = route("encomiendas.editar", id);
    });

    $(document).on("click", ".anular", function () {
        if (!confirm("¿Seguro de anular esta encomienda?")) return;

        let id = $(this).data("id");
        $.post(
            route("encomiendas.anular", id),
            { _token: csrf_token },
            function (res) {
                if (res.success) {
                    tabla.ajax.reload();
                }
            }
        ).fail(function () {
            alert("Error al anular la encomienda");
        });
    });
});

$(document).ready(async function () {
    UBIGEO = await $.get(route("ubigeos.todo"));
    initUbigeosReceptor();
});
