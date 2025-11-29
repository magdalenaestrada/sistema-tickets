document.addEventListener("DOMContentLoaded", function () {
    const horarioCards = document.querySelectorAll(".horario-card");
    const svgContainer = document.getElementById("svg-container");
    let selectedSeat = null;

    horarioCards.forEach((card) => {
        card.addEventListener("click", function () {
            const horarioId = this.dataset.horarioId;
            window.selectedHorario = horarioId;

            fetch(`/pasajes/horario/${horarioId}/asientos`)
                .then((res) => res.json())
                .then((data) => {
                    svgContainer.innerHTML = data.svg;

                    const svgEl = svgContainer.querySelector("svg");
                    if (!svgEl) {
                        console.error("SVG no encontrado en el contenedor");
                        return;
                    }

                    svgEl
                        .querySelectorAll(".seat-body, .seat-base")
                        .forEach((el) => {
                            el.setAttribute("fill", "#bfbfbf"); // gris
                        });

                    Object.keys(data.asientos).forEach((numero) => {
                        const estado = data.asientos[numero];
                        const g = svgEl.querySelector(`#seat-${numero}`);
                        if (!g) return;

                        const color =
                            estado === "ocupado"
                                ? "#e74c3c"
                                : estado === "reservado"
                                ? "#f1c40f"
                                : "#bfbfbf";

                        g.querySelectorAll(".seat-body, .seat-base").forEach(
                            (n) => {
                                n.setAttribute("fill", color);
                            }
                        );

                        g.style.cursor = "pointer";
                        g.dataset.estado = estado;

                        g.onclick = (ev) => {
                            if (g.dataset.estado !== "libre") {
                                return;
                            }

                            if (selectedSeat && selectedSeat !== g) {
                                selectedSeat
                                    .querySelectorAll(".seat-body, .seat-base")
                                    .forEach((n) =>
                                        n.setAttribute("fill", "#bfbfbf")
                                    );
                            }

                            const isSelected =
                                g.classList.toggle("selected-seat");
                            if (isSelected) {
                                g.querySelectorAll(
                                    ".seat-body, .seat-base"
                                ).forEach((n) =>
                                    n.setAttribute("fill", "#3498db")
                                );
                                selectedSeat = g;

                                const asientoId = numero;
                                console.log("Asiento seleccionado:", asientoId);
                            } else {
                                g.querySelectorAll(
                                    ".seat-body, .seat-base"
                                ).forEach((n) =>
                                    n.setAttribute("fill", "#bfbfbf")
                                );
                                selectedSeat = null;
                            }
                        };
                    });
                })
                .catch((err) => {
                    console.error("Error cargando asientos:", err);
                });
        });
    });

    let selectedSeats = [];

    document.addEventListener("click", function (e) {
        const seat = e.target.closest(".seat");
        if (!seat) return;
        const seatId = seat.id;
        if (selectedSeats.includes(seatId)) {
            selectedSeats = selectedSeats.filter((s) => s !== seatId);
            seat.classList.remove("selected");
        } else {
            selectedSeats.push(seatId);
            seat.classList.add("selected");
        }
        updateSellButton();
    });

    function updateSellButton() {
        const btn = document.getElementById("sell-button");

        if (selectedSeats.length > 0) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    }

    document
        .getElementById("sell-button")
        .addEventListener("click", function () {
            if (selectedSeats.length === 0) {
                alert("Selecciona al menos un asiento.");
                return;
            }
            const seats = selectedSeats
                .map((s) => s.replace("seat-", ""))
                .join(",");
            window.location.href = `/pasajes/vender?asientos=${seats}&horario=${window.selectedHorario}`;
        });
});
