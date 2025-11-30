document.addEventListener("DOMContentLoaded", function () {
    const horarioCards = document.querySelectorAll(".horario-card");
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");

    let selectedSeats = [];
    let currentHorarioId = null;

    horarioCards.forEach((card) => {
        card.addEventListener("click", function () {
            const horarioId = this.dataset.horarioId;
            currentHorarioId = horarioId;
            window.selectedHorario = horarioId;

            // Remover clase active de todas las tarjetas
            horarioCards.forEach((c) => c.classList.remove("active"));

            // Agregar clase active a la tarjeta seleccionada
            this.classList.add("active");

            selectedSeats = [];
            updateSellButton();

            fetch(`/pasajes/horario/${horarioId}/asientos`)
                .then((res) => res.json())
                .then((data) => {
                    svgContainer.innerHTML = data.svg;

                    const svgEl = svgContainer.querySelector("svg");
                    if (!svgEl) {
                        console.error("❌ SVG no encontrado");
                        return;
                    }

                    // Aplicar clases según estado
                    Object.keys(data.asientos).forEach((numero) => {
                        const estado = data.asientos[numero];
                        const g = svgEl.querySelector(`#seat-${numero}`);

                        if (!g) return;

                        // Limpiar clases previas
                        g.classList.remove(
                            "ocupado",
                            "reservado",
                            "libre",
                            "selected-seat"
                        );

                        // Agregar clase según estado
                        g.classList.add(estado);

                        // Guardar estado
                        g.dataset.estado = estado;
                        g.dataset.numero = numero;

                        // Configurar interactividad
                        if (estado === "libre") {
                            g.style.cursor = "pointer";
                            g.style.opacity = "1";

                            g.onclick = function (e) {
                                e.stopPropagation();
                                toggleSeatSelection(g, numero);
                            };
                        } else {
                            g.style.cursor = "not-allowed";
                            g.style.opacity = "0.7";

                            g.onclick = function (e) {
                                e.stopPropagation();
                                const mensaje =
                                    estado === "ocupado"
                                        ? "Este asiento está vendido"
                                        : "Este asiento está reservado";
                                alert(mensaje);
                            };
                        }
                    });
                })
                .catch((err) => {
                    console.error("❌ Error:", err);
                });
        });
    });

    function toggleSeatSelection(seatElement, numero) {
        const seatNum = parseInt(numero);
        const isSelected = selectedSeats.includes(seatNum);

        if (isSelected) {
            // Deseleccionar
            selectedSeats = selectedSeats.filter((s) => s !== seatNum);
            seatElement.classList.remove("selected-seat");
            seatElement.classList.add("libre");
        } else {
            // Seleccionar
            selectedSeats.push(seatNum);
            seatElement.classList.remove("libre");
            seatElement.classList.add("selected-seat");
        }

        updateSellButton();
    }

    function updateSellButton() {
        if (selectedSeats.length > 0) {
            sellButton.style.display = "block";
            sellButton.textContent = `Vender ${selectedSeats.length} asiento(s)`;
        } else {
            sellButton.style.display = "none";
        }
    }

    sellButton.addEventListener("click", async function () {
        if (selectedSeats.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No hay asientos seleccionados",
                text: "Selecciona al menos un asiento antes de continuar.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        if (!currentHorarioId) {
            Swal.fire({
                icon: "warning",
                title: "No hay horario seleccionado",
                text: "Selecciona un horario antes de continuar.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        try {
            const res = await fetch("/caja/verificar");
            const data = await res.json();

            if (!data.abierta) {
                Swal.fire({
                    icon: "warning",
                    title: "Caja no abierta",
                    text: "Aún no has abierto caja. No puedes vender pasajes.",
                    confirmButtonText: "Entendido",
                });
                return;
            }

            const seats = selectedSeats.sort((a, b) => a - b).join(",");
            window.location.href = `/pasajes/vender?asientos=${seats}&horario=${currentHorarioId}`;
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo verificar el estado de la caja.",
                confirmButtonText: "Entendido",
            });
        }
    });
});
