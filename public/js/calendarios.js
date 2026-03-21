$(document).ready(async function () {
    const calendarEl = document.getElementById("calendar");
    window.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: "es",
        height: 400,
        fixedWeekCount: false,
        headerToolbar: {
            left: "prev",
            center: "title",
            right: "next",
        },
        events: eventosLaravel,
        eventContent(info) {
            const persona = info.event.extendedProps.persona ?? "";
            const iniciales = persona
                .split(" ")
                .map((w) => w[0])
                .join("")
                .substring(0, 2)
                .toUpperCase();
            const foto = info.event.extendedProps.foto ?? null;

            if (foto) {
                return {
                    html: `<div class="cumple-dot"><img src="${foto}" alt="${persona}"></div>`,
                };
            }
            return {
                html: `<div class="cumple-dot-default">${iniciales}</div>`,
            };
        },
        eventClick(info) {
            const persona =
                info.event.extendedProps.persona ?? info.event.title;
            const edad = info.event.extendedProps.edad ?? null;
            Swal.fire({
                icon: "info",
                title: "🎂 Cumpleaños",
                text: `${persona}${edad ? " — " + edad + " años" : ""}`,
                timer: 3000,
                showConfirmButton: false,
            });
        },
    });

    window.calendar.render();
    setTimeout(() => {
        calendar.updateSize();
    }, 50);

    window.renderProximos = function () {
        const hoy = new Date();
        const finVentana = new Date(hoy);
        finVentana.setDate(hoy.getDate() + 10);

        function parseFechaLocal(str) {
            const [y, m, d] = str.substring(0, 10).split("-").map(Number);
            return new Date(y, m - 1, d);
        }

        const proximos = eventosLaravel
            .map((ev) => {
                const fechaBase = parseFechaLocal(ev.start);
                let proxima = new Date(
                    hoy.getFullYear(),
                    fechaBase.getMonth(),
                    fechaBase.getDate(),
                );
                if (proxima < hoy) proxima.setFullYear(hoy.getFullYear() + 1);
                return {
                    ...ev,
                    proximaFecha: proxima,
                };
            })
            .filter((ev) => ev.proximaFecha <= finVentana)
            .sort((a, b) => a.proximaFecha - b.proximaFecha)
            .slice(0, 10);

        const container = document.getElementById("proximosCumple");
        if (!proximos.length) {
            container.innerHTML = `<p style="font-size:13px;color:var(--subtexto)">No hay cumpleaños próximos.</p>`;
            return;
        }

        const meses = [
            "Ene",
            "Feb",
            "Mar",
            "Abr",
            "May",
            "Jun",
            "Jul",
            "Ago",
            "Sep",
            "Oct",
            "Nov",
            "Dic",
        ];

        container.innerHTML =
            `<div class="proximos-list">` +
            proximos
                .map((ev) => {
                    const d = ev.proximaFecha;
                    const fechaStr = `${String(d.getDate()).padStart(2, "0")} ${meses[d.getMonth()]}`;
                    const persona = ev.extendedProps?.persona ?? ev.title ?? "";
                    const cargo = ev.extendedProps?.cargo ?? "";
                    const foto = ev.extendedProps?.foto ?? null;
                    const iniciales = persona
                        .split(" ")
                        .map((w) => w[0])
                        .join("")
                        .substring(0, 2)
                        .toUpperCase();

                    const avatar = foto
                        ? `<div class="proximo-avatar"><img src="${foto}" alt="${persona}"></div>`
                        : `<div class="proximo-avatar">${iniciales}</div>`;

                    return `
                    <div class="proximo-item">
                        ${avatar}
                        <div class="proximo-info">
                            <div class="proximo-fecha">${fechaStr}</div>
                            <div class="proximo-nombre">${persona}${cargo ? " (" + cargo + ")" : ""}</div>
                        </div>
                    </div>`;
                })
                .join("") +
            `</div>`;
    };

    renderProximos();
});
