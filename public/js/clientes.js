$(document).ready(function () {
    const modal = new bootstrap.Modal($("#modalCliente")[0]);

    const tabla = $("#tablaClientes").DataTable({
        processing: true,
        serverSide: false,
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
                title: "Nombre",
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
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: () => lucide.createIcons(),
    });

    $("#filtroDocumento").on("keyup change", function () {
        tabla.column(0).search(this.value).draw();
    });

    $("#filtroNombres").on("keyup change", function () {
        tabla.column(1).search(this.value).draw();
    });

    initUbigeos("#departamento_id", "#provincia_id", "#distrito_id");

    function toggleConductor(cargoVal, cargoDesc = "") {
        const $conductor = $(".conductor");
        if (cargoVal == 16 || cargoDesc.toLowerCase().includes("conductor")) {
            $conductor.removeAttr("hidden").show();
        } else {
            $conductor.attr("hidden", true).hide();
            $("#tipo_licencia_id, #licencia_conducir").val("");
        }
    }

    function cargarListasCliente(callback = null) {
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
            fillSelect(
                "#tipo_documento_id",
                res.tipos_documento,
                "Seleccione",
                "codigo"
            );
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
                "warning"
            );

        $("#btnBuscarDocumento")
            .prop("disabled", true)
            .html('<i data-lucide="search"></i>');
        lucide.createIcons();

        $.getJSON(route("clientes.buscar", { documento }))
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

    $("#celular").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 9);
    });

    // Nuevo empleado
    $("#btnNuevoCliente").click(() => {
        $("#formCliente")[0].reset();
        $("#empleado_id, #usuario, #password").val("");
        $("#seccionUsuario").hide();
        $("#chkUsuario").prop("checked", false);
        $(".conductor").attr("hidden", true).hide();
        $("#cargo_id, #sucursal_id").val(null).trigger("change");

        cargarListasCliente(() => modal.show());
    });

    function cargarCliente(url, viewOnly = false) {
        cargarListasCliente(() => {
            $.get(url, (res) => {
                const p = res.persona ?? {};
                p.fecha_nacimiento;
                p.distrito_id;
                p.tipo_documento_id;
                console.log("Persona:", p);

                $("#empleado_id").val(res.id);
                $("#documento").val(p.documento ?? "");
                $("#nombres").val(p.nombres ?? "");
                $("#apellidos").val(p.apellidos ?? "");
                $("#correo").val(p.correo ?? "");
                $("#telefono").val(p.telefono ?? "");
                $("#celular").val(p.celular ?? "");
                $("#direccion").val(p.direccion ?? "");
                if (p.fecha_nacimiento) {
                    $("#fecha_nacimiento").val(
                        p.fecha_nacimiento.substring(0, 10)
                    );
                }
                $("#tipo_documento_id")
                    .val(p.tipo_documento_id ?? "")
                    .trigger("change");

                if (p.distrito_id) {
                    cargarUbigeoDesdeDistrito(p.distrito_id);
                }

                modal.show();
                lucide.createIcons();
            });
        });
    }

    function cargarUbigeoDesdeDistrito(distrito_id) {
        axios.get(route("ubigeos.byDistrito", distrito_id)).then(({ data }) => {
            $("#departamento_id").val(data.departamento_id).trigger("change");

            setTimeout(() => {
                $("#provincia_id").val(data.provincia_id).trigger("change");
            }, 300);

            setTimeout(() => {
                $("#distrito_id").val(data.distrito_id).trigger("change");
            }, 600);
        });
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
                            "error"
                        );
                    },
                });
            }
        });
    });

    $("#formCliente").on("submit", function (e) {
        e.preventDefault();

        $.ajax({
            url: route("clientes.store"),
            type: "POST",
            data: $(this).serialize(),
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
            '<i data-lucide="user"></i> Registrar / Editar Cliente'
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
