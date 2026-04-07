document.addEventListener("DOMContentLoaded", function () {
    const formFiltros = document.getElementById("form-filtros-caja");
    const contenedorTabla = document.getElementById("contenedor-tabla-cajas");
    const contenedorResumen = document.getElementById(
        "contenedor-resumen-cajas",
    );
    const contenedorBotonMasivo = document.getElementById(
        "contenedor-boton-masivo",
    );
    const btnLimpiar = document.getElementById("btn-limpiar-filtros");

    if (!formFiltros) return;

    async function cargarFiltros(url = null) {
        try {
            const formData = new FormData(formFiltros);
            const params = new URLSearchParams(formData);

            const destino = url || `${formFiltros.action}?${params.toString()}`;

            const response = await fetch(destino, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (!response.ok) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudieron cargar las cajas.",
                });
                return;
            }

            if (contenedorTabla && data.tabla) {
                contenedorTabla.innerHTML = data.tabla;
            }

            if (contenedorResumen && data.resumen) {
                contenedorResumen.innerHTML = data.resumen;
            }

            if (contenedorBotonMasivo && data.botonMasivo) {
                contenedorBotonMasivo.innerHTML = data.botonMasivo;
            }

            window.history.pushState({}, "", destino);
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo actualizar la información.",
            });
        }
    }

    formFiltros.addEventListener("submit", function (e) {
        e.preventDefault();
        cargarFiltros();
    });

    formFiltros.querySelectorAll("select").forEach((elemento) => {
        elemento.addEventListener("change", function () {
            cargarFiltros();
        });
    });

    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", function () {
            formFiltros.reset();
            cargarFiltros(formFiltros.action);
        });
    }

    document.addEventListener("click", function (e) {
        const link = e.target.closest("#contenedor-tabla-cajas .pagination a");
        if (!link) return;

        e.preventDefault();
        cargarFiltros(link.href);
    });
});
