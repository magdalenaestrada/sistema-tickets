document.addEventListener("DOMContentLoaded", function () {

    cargarEncomiendas();

    function cargarEncomiendas() {
        fetch("/asignar-encomiendas/datatable/no-asignadas")
            .then(r => r.json())
            .then(data => {
                let tbody = document.querySelector("#tablaEncomiendas tbody");
                tbody.innerHTML = "";

                data.forEach(e => {
                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <input type="checkbox" class="chkEncomienda" value="${e.id}">
                            </td>
                            <td>${e.id}</td>
                            <td>${e.origen}</td>
                            <td>${e.destino}</td>
                            <td>${e.fecha_creacion}</td>
                            <td>${e.total}</td>
                        </tr>
                    `;
                });
            });
    }

    // 🔍 FILTRO EN TIEMPO REAL
    document.getElementById("inputBuscar").addEventListener("keyup", function () {
        let filtro = this.value.toLowerCase();
        document.querySelectorAll("#tablaEncomiendas tbody tr").forEach(tr => {
            tr.style.display = tr.innerText.toLowerCase().includes(filtro) ? "" : "none";
        });
    });

    // 📨 Enviar asignación
    document.getElementById("formAsignacion").addEventListener("submit", function (e) {
        e.preventDefault();

        let seleccionadas = [...document.querySelectorAll(".chkEncomienda:checked")].map(c => c.value);

        if (seleccionadas.length === 0) {
            alert("Seleccione al menos una encomienda");
            return;
        }

        let formData = new FormData(this);
        seleccionadas.forEach(id => formData.append("encomiendas[]", id));

        fetch("/asignar-encomiendas", {
            method: "POST",
            body: formData
        })
            .then(r => r.json())
            .then(resp => {
                alert(resp.message);
                cargarEncomiendas();
            });
    });

});
