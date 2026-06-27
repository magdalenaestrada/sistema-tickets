document.addEventListener("DOMContentLoaded", function () {
    const tipoSalida = document.getElementById("tipo_salida");
    const campoMontoSimple = document.getElementById("campo_monto_simple");
    const campoMontoEfectivo = document.getElementById("campo_monto_efectivo");
    const campoMontoDigital = document.getElementById("campo_monto_digital");
    const campoBilletera = document.getElementById("campo_billetera");

    function ocultarSalida() {
        if (campoMontoSimple) {
            campoMontoSimple.classList.add("d-none");
            campoMontoSimple.querySelector("input").value = "";
        }

        if (campoMontoEfectivo) {
            campoMontoEfectivo.classList.add("d-none");
            campoMontoEfectivo.querySelector("input").value = "";
        }

        if (campoMontoDigital) {
            campoMontoDigital.classList.add("d-none");
            campoMontoDigital.querySelector("input").value = "";
        }

        if (campoBilletera) {
            campoBilletera.classList.add("d-none");
            campoBilletera.querySelector("select").value = "";
        }
    }

    function actualizarSalida() {
        ocultarSalida();
        if (!tipoSalida) return;

        if (tipoSalida.value === "1") {
            campoMontoSimple.classList.remove("d-none");
        } else if (tipoSalida.value === "2") {
            campoMontoSimple.classList.remove("d-none");
            campoBilletera.classList.remove("d-none");
        } else if (tipoSalida.value === "3") {
            campoMontoEfectivo.classList.remove("d-none");
            campoMontoDigital.classList.remove("d-none");
            campoBilletera.classList.remove("d-none");
        }
    }

    document.querySelectorAll(".btn-anular-ticket").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const form = this.closest("form");

            Swal.fire({
                title: "¿Anular venta?",
                text: "Esta acción anulará el ticket y la venta asociada.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, anular",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    if (tipoSalida) {
        tipoSalida.addEventListener("change", actualizarSalida);
        actualizarSalida();
    }

    const tipoIngreso = document.getElementById("tipo_ingreso");
    const ingresoMontoSimple = document.getElementById("ingreso_monto_simple");
    const ingresoMontoEfectivo = document.getElementById(
        "ingreso_monto_efectivo",
    );
    const ingresoMontoDigital = document.getElementById(
        "ingreso_monto_digital",
    );
    const ingresoBilletera = document.getElementById("ingreso_billetera");

    function ocultarIngreso() {
        if (ingresoMontoSimple) {
            ingresoMontoSimple.classList.add("d-none");
            ingresoMontoSimple.querySelector("input").value = "";
        }

        if (ingresoMontoEfectivo) {
            ingresoMontoEfectivo.classList.add("d-none");
            ingresoMontoEfectivo.querySelector("input").value = "";
        }

        if (ingresoMontoDigital) {
            ingresoMontoDigital.classList.add("d-none");
            ingresoMontoDigital.querySelector("input").value = "";
        }
        if (ingresoBilletera) {
            ingresoBilletera.classList.add("d-none");
            ingresoBilletera.querySelector("select").value = "";
        }
    }

    function actualizarIngreso() {
        ocultarIngreso();
        if (!tipoIngreso) return;

        if (tipoIngreso.value === "1") {
            ingresoMontoSimple.classList.remove("d-none");
        } else if (tipoIngreso.value === "2") {
            ingresoMontoSimple.classList.remove("d-none");
            ingresoBilletera.classList.remove("d-none");
        } else if (tipoIngreso.value === "3") {
            ingresoMontoEfectivo.classList.remove("d-none");
            ingresoMontoDigital.classList.remove("d-none");
            ingresoBilletera.classList.remove("d-none");
        }
    }

    if (tipoIngreso) {
        tipoIngreso.addEventListener("change", actualizarIngreso);
        actualizarIngreso();
    }

    const formIngreso = document.getElementById("form-ingreso");
    const tablaContenedor = document.getElementById(
        "contenedor-tabla-movimientos",
    );

    if (formIngreso) {
        formIngreso.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(formIngreso);

            try {
                const response = await fetch(formIngreso.action, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        Accept: "application/json",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        let errores = Object.values(data.errors)
                            .flat()
                            .join("<br>");
                        Swal.fire({
                            icon: "error",
                            title: "Errores de validación",
                            html: errores,
                        });
                        return;
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            data.message ||
                            "Ocurrió un error al registrar el ingreso.",
                    });
                    return;
                }

                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false,
                });

                formIngreso.reset();
                actualizarIngreso();

                const modalEl = document.getElementById("modalIngreso");
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                modal.hide();
                if (tablaContenedor && data.tabla) {
                    tablaContenedor.innerHTML = data.tabla;
                }

                document.getElementById("total_ingresos").textContent =
                    `S/ ${parseFloat(data.total_ingresos).toFixed(2)}`;

                document.getElementById("total_egresos").textContent =
                    `S/ ${parseFloat(data.total_salidas).toFixed(2)}`;

                document.getElementById("efectivo_esperado").textContent =
                    `S/ ${parseFloat(data.efectivo_esperado).toFixed(2)}`;

                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error inesperado",
                    text: "No se pudo procesar la solicitud.",
                });
                console.error(error);
            }
        });
    }

    const modalIngreso = document.getElementById("modalIngreso");

    if (modalIngreso) {
        modalIngreso.addEventListener("show.bs.modal", function () {
            const form = modalIngreso.querySelector("form");
            if (form) form.reset();

            document.querySelectorAll(".ingreso-campo").forEach((el) => {
                el.classList.add("d-none");

                const input = el.querySelector("input");
                const select = el.querySelector("select");

                if (input) input.value = "";
                if (select) select.value = "";
            });
        });
    }

    const salidaSimple = document.getElementById("salida_monto_simple");
    const salidaEfectivo = document.getElementById("salida_monto_efectivo");
    const salidaDigital = document.getElementById("salida_monto_digital");
    const salidaBilletera = document.getElementById("salida_billetera");

    function actualizarSalida() {
        ocultarSalida();

        if (!tipoSalida) return;

        const value = tipoSalida.value;

        if (value === "1") {
            salidaSimple?.classList.remove("d-none");
        } else if (value === "2") {
            salidaSimple?.classList.remove("d-none");
            salidaBilletera?.classList.remove("d-none");
        } else if (value === "3") {
            salidaEfectivo?.classList.remove("d-none");
            salidaDigital?.classList.remove("d-none");
            salidaBilletera?.classList.remove("d-none");
        }
    }

    tipoSalida?.addEventListener("change", actualizarSalida);
    document
        .getElementById("modalSalida")
        ?.addEventListener("show.bs.modal", function () {
            const form = document.getElementById("form-salida");
            if (form) form.reset();

            ocultarSalida();
        });
});
