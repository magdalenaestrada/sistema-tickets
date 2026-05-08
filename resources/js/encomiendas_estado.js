$(function () {
    let tabla = $("#tablaEncomiendas").DataTable({
        ajax: route("encomiendas.datatable.asignadas"),
        columns: [
            { data: "id" },
            { data: "dni_receptor" },
            { data: "receptor" },
            { data: "emisor" },
            { data: "origen" },
            { data: "destino" },
            { data: "total" },
            { data: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        order: [[0, "desc"]],
        
        dom: "rtip",
        lengthChange: false,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#filtroReceptor").on("keyup", function () {
        tabla.columns([1, 2]).search(this.value).draw();
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
            },
        ).fail(function () {
            alert("Error al anular la encomienda");
        });
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
                        "encomiendas.crear-encomienda",
                    );
                });
        });
    }

    $(document).on("click", ".entregar", function () {
        let boton = $(this);
        let id = boton.data("id");

        $.ajax({
            url: route("encomiendas.entregar", id),
            type: "PUT",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                boton.prop("disabled", true);
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Entregado",
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    tabla.ajax.reload(null, false);
                }
            },
            error: function () {
                boton.prop("disabled", false);
                Swal.fire("Error", "No se pudo entregar", "error");
            },
        });
    });
});
