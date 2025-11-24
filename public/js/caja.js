document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("btnAbrirCaja");

    if (btn) {
        btn.addEventListener("click", () => {
            const modal = new bootstrap.Modal(
                document.getElementById("createCaja")
            );
            modal.show();
        });
    }
});
