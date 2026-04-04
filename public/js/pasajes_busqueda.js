document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    const filtroDNI = document.getElementById("filtroDNI");
    const filtroFecha = document.getElementById("filtroFecha");
    const filtroOrigen = document.getElementById("filtroOrigen");
    const filtroDestino = document.getElementById("filtroDestino");
    const filtroEstado = document.getElementById("filtroEstado");
    const btnBuscar = document.getElementById("btnBuscar");
    const btnLimpiar = document.getElementById("btnLimpiar");
    const contenedorResultados = document.getElementById(
        "contenedorResultados",
    );

    if (document.querySelector("#filtroOrigen")) {
        new TomSelect("#filtroOrigen", {
            create: false,
            allowEmptyOption: true,
            placeholder: "Buscar origen",
        });
    }

    if (document.querySelector("#filtroDestino")) {
        new TomSelect("#filtroDestino", {
            create: false,
            allowEmptyOption: true,
            placeholder: "Buscar destino",
        });
    }

    if (document.querySelector("#filtroEstado")) {
        new TomSelect("#filtroEstado", {
            create: false,
            allowEmptyOption: true,
            placeholder: "Buscar estado",
        });
    }

    let debounceTimer = null;

    function obtenerFiltros() {
        const params = new URLSearchParams();
        params.append("documento", filtroDNI?.value || "");
        params.append("fecha", filtroFecha?.value || "");
        params.append("origen_id", filtroOrigen?.value || "");
        params.append("destino_id", filtroDestino?.value || "");
        params.append("estado", filtroEstado?.value || "");
        return params.toString();
    }

    function cargarResultados(url = null) {
        const dni = (document.getElementById("filtroDNI")?.value || "").trim();

        if (dni.length > 0 && dni.length < 3) {
            return;
        }

        if (!contenedorResultados) return;

        const finalUrl =
            url || `${route("pasajes.listar")}?${obtenerFiltros()}`;

        fetch(finalUrl, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html",
            },
        })
            .then(async (response) => {
                const text = await response.text();

                if (!response.ok) {
                    console.error("Error AJAX:", text);
                    throw new Error("No se pudo cargar la búsqueda.");
                }

                return text;
            })
            .then((html) => {
                contenedorResultados.innerHTML = html;

                if (window.lucide) {
                    lucide.createIcons();
                }

                enlazarBotonesAccion();
                enlazarPaginacionAjax();
            })
            .catch((error) => {
                Swal.fire(
                    "Error",
                    error.message || "Error al cargar resultados.",
                    "error",
                );
            });
    }

    function enlazarPaginacionAjax() {
        if (!contenedorResultados) return;

        contenedorResultados.querySelectorAll(".pagination a").forEach((a) => {
            a.addEventListener("click", function (e) {
                e.preventDefault();
                cargarResultados(this.href);
            });
        });
    }

    function ejecutarAccion(url, titulo, texto) {
        Swal.fire({
            title: titulo,
            text: texto,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, confirmar",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            })
                .then(async (response) => {
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message || "No se pudo procesar la acción.",
                        );
                    }

                    return data;
                })
                .then((data) => {
                    Swal.fire(
                        "Éxito",
                        data.message || "Operación realizada correctamente.",
                        "success",
                    );
                    cargarResultados();
                })
                .catch((error) => {
                    Swal.fire(
                        "Error",
                        error.message || "Ocurrió un error.",
                        "error",
                    );
                });
        });
    }

    function enlazarBotonesAccion() {
        document.querySelectorAll(".btn-abordo").forEach((btn) => {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;

                ejecutarAccion(
                    route("pasajes.abordo", id),
                    "¿Marcar como abordó?",
                    "El pasajero será marcado como abordó.",
                );
            });
        });

        document.querySelectorAll(".btn-no-abordo").forEach((btn) => {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;

                ejecutarAccion(
                    route("pasajes.noAbordo", id),
                    "¿Marcar como no abordó?",
                    "El pasajero será marcado como no abordó.",
                );
            });
        });
    }

    filtroDNI?.addEventListener("input", debounceBuscar);
    filtroFecha?.addEventListener("change", debounceBuscar);
    filtroOrigen?.addEventListener("change", debounceBuscar);
    filtroDestino?.addEventListener("change", debounceBuscar);
    filtroEstado?.addEventListener("change", debounceBuscar);

    btnBuscar?.addEventListener("click", function (e) {
        e.preventDefault();
        cargarResultados();
    });

    btnLimpiar?.addEventListener("click", function (e) {
        e.preventDefault();

        if (filtroDNI) filtroDNI.value = "";
        if (filtroFecha) filtroFecha.value = "";

        if (filtroOrigen?.tomselect) {
            filtroOrigen.tomselect.clear();
        } else if (filtroOrigen) {
            filtroOrigen.value = "";
        }

        if (filtroDestino?.tomselect) {
            filtroDestino.tomselect.clear();
        } else if (filtroDestino) {
            filtroDestino.value = "";
        }

        if (filtroEstado?.tomselect) {
            filtroEstado.tomselect.clear();
        } else if (filtroEstado) {
            filtroEstado.value = "";
        }

        cargarResultados();
    });

    enlazarBotonesAccion();
    enlazarPaginacionAjax();
});
