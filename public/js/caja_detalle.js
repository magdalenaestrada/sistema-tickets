document.addEventListener("DOMContentLoaded", function () {
    // ===========================================================
    // Helpers genéricos
    // ===========================================================

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute("content") : null;
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: "error",
            title: "Ocurrió un error",
            text:
                mensaje ||
                "No se pudo completar la operación. Intenta nuevamente.",
        });
    }

    function bloquearBoton(form, bloquear) {
        if (!form) return;
        const btn = form.querySelector('[type="submit"]');
        if (!btn) return;

        btn.disabled = bloquear;

        if (bloquear) {
            btn.dataset.textoOriginal = btn.innerHTML;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
        } else if (btn.dataset.textoOriginal) {
            btn.innerHTML = btn.dataset.textoOriginal;
            delete btn.dataset.textoOriginal;
        }
    }

    // Envía el formulario por fetch y SIEMPRE da feedback al usuario.
    // Devuelve los datos de la respuesta si todo salió bien, o null si hubo error
    // (en cuyo caso ya se mostró el mensaje correspondiente).
    async function enviarFormulario(form) {
        const token = getCsrfToken();
        if (!token) {
            mostrarError(
                "No se encontró el token CSRF. Recarga la página e intenta de nuevo.",
            );
            return null;
        }

        const formData = new FormData(form);
        let response;

        try {
            response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
                body: formData,
            });
        } catch (error) {
            console.error(error);
            mostrarError(
                "No se pudo conectar con el servidor. Revisa tu conexión e intenta de nuevo.",
            );
            return null;
        }

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            console.error("Respuesta no es JSON válido:", error);
        }

        if (!response.ok) {
            let mensaje = data?.message;

            if (!mensaje && data?.errors) {
                mensaje = Object.values(data.errors).flat().join("\n");
            }

            mostrarError(mensaje);
            return null;
        }

        return data;
    }

    function actualizarTotales(data) {
        if (!data) return;

        console.log(data);
        
        const tablaContenedor = document.getElementById(
            "contenedor-tabla-movimientos",
        );
        if (tablaContenedor && data.tabla) {
            tablaContenedor.innerHTML = data.tabla;
        }

        const totalIngresos = document.getElementById("total_ingresos");
        const totalEgresos = document.getElementById("total_egresos");
        const efectivoEsperado = document.getElementById("efectivo_esperado");
        const totalYape = document.getElementById("total_yape");
        const totalPlin = document.getElementById("total_plin");
        const totalTransferencia = document.getElementById(
            "total_transferencia",
        );
        const totalTarjeta = document.getElementById("total_tarjeta");
        const totalEfectivo = document.getElementById("total_efectivo");

        if (totalYape && data.total_yape !== undefined) {
            totalYape.textContent = `S/ ${parseFloat(data.total_yape).toFixed(2)}`;
        }

        if (totalPlin && data.total_plin !== undefined) {
            totalPlin.textContent = `S/ ${parseFloat(data.total_plin).toFixed(2)}`;
        }

        if (totalTransferencia && data.total_transferencia !== undefined) {
            totalTransferencia.textContent = `S/ ${parseFloat(data.total_transferencia).toFixed(2)}`;
        }

        if (totalTarjeta && data.total_tarjeta !== undefined) {
            totalTarjeta.textContent = `S/ ${parseFloat(data.total_tarjeta).toFixed(2)}`;
        }

        if (totalEfectivo && data.total_efectivo !== undefined) {
            totalEfectivo.textContent = `S/ ${parseFloat(data.total_efectivo).toFixed(2)}`;
        }
        if (totalIngresos && data.total_ingresos !== undefined) {
            totalIngresos.textContent = `S/ ${parseFloat(data.total_ingresos).toFixed(2)}`;
        }

        if (totalEgresos && data.total_salidas !== undefined) {
            totalEgresos.textContent = `S/ ${parseFloat(data.total_salidas).toFixed(2)}`;
        }

        if (efectivoEsperado && data.total_efectivo !== undefined) {
            efectivoEsperado.textContent = `S/ ${parseFloat(data.total_efectivo).toFixed(2)}`;
        }

        if (typeof lucide !== "undefined") {
            lucide.createIcons();
        }
    }

    function limpiarCampos(elementos) {
        elementos.forEach((el) => {
            if (!el) return;
            el.classList.add("d-none");
            el.querySelectorAll("input, select").forEach((input) => {
                input.value = "";
            });
        });
    }

    // ===========================================================
    // SALIDA (egreso)
    // ===========================================================

    const tipoSalida = document.getElementById("tipo_salida");
    const campoMontoSimple = document.getElementById("salida_monto_simple");
    const campoMontoEfectivo = document.getElementById("salida_monto_efectivo");
    const campoMontoDigital = document.getElementById("salida_monto_digital");
    const campoBilletera = document.getElementById("salida_billetera");
    const formSalida = document.getElementById("form-salida");
    const modalSalidaElemento = document.getElementById("modalSalida");

    function ocultarSalida() {
        limpiarCampos([
            campoMontoSimple,
            campoMontoEfectivo,
            campoMontoDigital,
            campoBilletera,
        ]);
    }

    function actualizarSalida() {
        ocultarSalida();
        if (!tipoSalida) return;

        const value = tipoSalida.value;

        if (value === "1") {
            campoMontoSimple?.classList.remove("d-none");
        }

        if (value === "2") {
            campoMontoSimple?.classList.remove("d-none");
            campoBilletera?.classList.remove("d-none");
        }

        if (value === "3") {
            campoMontoEfectivo?.classList.remove("d-none");
            campoMontoDigital?.classList.remove("d-none");
            campoBilletera?.classList.remove("d-none");
        }
    }

    if (tipoSalida) {
        tipoSalida.addEventListener("change", actualizarSalida);
        actualizarSalida();
    }

    if (modalSalidaElemento) {
        modalSalidaElemento.addEventListener("shown.bs.modal", function () {
            const form = this.querySelector("form");
            if (form) form.reset();
            ocultarSalida();
        });

        modalSalidaElemento.addEventListener("hidden.bs.modal", function () {
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("padding-right");
            document
                .querySelectorAll(".modal-backdrop")
                .forEach((el) => el.remove());
        });
    }

    if (formSalida) {
        formSalida.addEventListener("submit", async function (e) {
            e.preventDefault();

            bloquearBoton(formSalida, true);
            const data = await enviarFormulario(formSalida);
            bloquearBoton(formSalida, false);

            if (!data) return;

            const modal =
                bootstrap.Modal.getOrCreateInstance(modalSalidaElemento);
            modal.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            formSalida.reset();
            ocultarSalida();
            actualizarTotales(data);
        });
    }

    // ===========================================================
    // INGRESO
    // ===========================================================

    const tipoIngreso = document.getElementById("tipo_ingreso");
    const ingresoMontoSimple = document.getElementById("ingreso_monto_simple");
    const ingresoMontoEfectivo = document.getElementById(
        "ingreso_monto_efectivo",
    );
    const ingresoMontoDigital = document.getElementById(
        "ingreso_monto_digital",
    );
    const ingresoBilletera = document.getElementById("ingreso_billetera");
    const formIngreso = document.getElementById("form-ingreso");
    const modalIngresoElemento = document.getElementById("modalIngreso");
    const modalIngreso = modalIngresoElemento
        ? bootstrap.Modal.getOrCreateInstance(modalIngresoElemento)
        : null;

    function ocultarIngreso() {
        limpiarCampos([
            ingresoMontoSimple,
            ingresoMontoEfectivo,
            ingresoMontoDigital,
            ingresoBilletera,
        ]);
    }

    function actualizarIngreso() {
        ocultarIngreso();
        if (!tipoIngreso) return;

        if (tipoIngreso.value === "1") {
            ingresoMontoSimple?.classList.remove("d-none");
        } else if (tipoIngreso.value === "2") {
            ingresoMontoSimple?.classList.remove("d-none");
            ingresoBilletera?.classList.remove("d-none");
        } else if (tipoIngreso.value === "3") {
            ingresoMontoEfectivo?.classList.remove("d-none");
            ingresoMontoDigital?.classList.remove("d-none");
            ingresoBilletera?.classList.remove("d-none");
        }
    }

    if (tipoIngreso) {
        tipoIngreso.addEventListener("change", actualizarIngreso);
        actualizarIngreso();
    }

    if (modalIngresoElemento) {
        modalIngresoElemento.addEventListener("shown.bs.modal", function () {
            const form = this.querySelector("form");
            if (form) form.reset();
            ocultarIngreso();
        });

        modalIngresoElemento.addEventListener("hidden.bs.modal", function () {
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("padding-right");
            document
                .querySelectorAll(".modal-backdrop")
                .forEach((el) => el.remove());
        });
    }

    if (formIngreso) {
        formIngreso.addEventListener("submit", async function (e) {
            e.preventDefault();

            bloquearBoton(formIngreso, true);
            const data = await enviarFormulario(formIngreso);
            bloquearBoton(formIngreso, false);

            if (!data) return; // ya se mostró el error, el modal queda abierto para corregir

            modalIngreso?.hide();

            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
            });

            formIngreso.reset();
            ocultarIngreso();
            actualizarTotales(data);
        });
    }

    // ===========================================================
    // Anular ticket
    // ===========================================================

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
    $(document).on("submit", ".cerrar-caja-form", function (e) {
        e.preventDefault();

        let form = $(this);

        Swal.fire({
            title: "¿Cerrar caja?",
            text: "Una vez cerrada no podrás seguir registrando movimientos.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cerrar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: form.serialize(),
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Caja cerrada",
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    let mensaje = "Ocurrió un error.";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: mensaje,
                    });
                },
            });
        });
    });
});
