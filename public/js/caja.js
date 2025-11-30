$(document).ready(function () {
    $("#btnAbrirCaja").click(function () {
        $.ajax({
            url: route("caja.verificar"),
            type: "GET",
            success: function (res) {
                if (res.abierta) {
                    Swal.fire({
                        icon: "warning",
                        title: "Caja ya abierta",
                        text: "Ya tienes una caja abierta en esta sucursal. Debes cerrarla antes de abrir otra.",
                    });
                } else {
                    $("#modalCrearCaja").modal("show");
                }
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo verificar el estado de la caja.",
                });
            },
        });
    });
});
