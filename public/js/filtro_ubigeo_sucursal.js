// Función para cargar toda la jerarquía: departamentos → provincias → distritos con sucursales
function cargarUbigeosConSucursales(
    depSelectId = "#departamento_id",
    provSelectId = "#provincia_id",
    distSelectId = "#distrito_id",
    sucSelectId = "#sucursal_id",
    selectedDepartamento = null,
    selectedProvincia = null,
    selectedDistrito = null
) {
    $.get("/ubigeos/ubigeos-con-sucursales", function (departamentos) {
        const depSelect = $(depSelectId);
        depSelect
            .empty()
            .append('<option value="">Seleccione un departamento</option>');
        departamentos.forEach((dep) =>
            depSelect.append(`<option value="${dep.id}">${dep.nombre}</option>`)
        );
        if (selectedDepartamento) depSelect.val(selectedDepartamento);

        const provSelect = $(provSelectId);
        provSelect
            .empty()
            .append('<option value="">Seleccione una provincia</option>');
        const provincias =
            departamentos.find((d) => d.id == selectedDepartamento)
                ?.provincias || [];
        provincias.forEach((p) =>
            provSelect.append(`<option value="${p.id}">${p.nombre}</option>`)
        );
        if (selectedProvincia) provSelect.val(selectedProvincia);

        // Distritos
        const distSelect = $(distSelectId);
        distSelect
            .empty()
            .append('<option value="">Seleccione un distrito</option>');
        const distritos =
            provincias.find((p) => p.id == selectedProvincia)?.distritos || [];
        distritos.forEach((d) =>
            distSelect.append(`<option value="${d.id}">${d.nombre}</option>`)
        );
        if (selectedDistrito) distSelect.val(selectedDistrito);

        // Sucursales (si ya hay distrito seleccionado)
        if (selectedDistrito) {
            cargarSucursales(selectedDistrito, sucSelectId);
        } else {
            $(sucSelectId)
                .empty()
                .append('<option value="">Seleccione una sucursal</option>');
        }
    });
}

// Función para cargar sucursales de un distrito
function cargarSucursales(distritoId, sucSelectId = "#sucursal_id") {
    const sucursalSelect = $(sucSelectId);
    sucursalSelect
        .empty()
        .append('<option value="">Seleccione una sucursal</option>');

    if (!distritoId) return;

    $.get(`/ubigeos/sucursales/${distritoId}`, function (sucursales) {
        sucursales.forEach((s) =>
            sucursalSelect.append(
                `<option value="${s.id}">${s.nombre_comercial}</option>`
            )
        );
    });
}

// Función para inicializar select dependientes
function initUbigeos(depSelectId, provSelectId, distSelectId, sucSelectId) {
    $(depSelectId).on("change", function () {
        const depId = $(this).val();
        cargarUbigeosConSucursales(
            depSelectId,
            provSelectId,
            distSelectId,
            sucSelectId,
            depId
        );
    });

    $(provSelectId).on("change", function () {
        const provId = $(this).val();
        const depId = $(depSelectId).val();
        cargarUbigeosConSucursales(
            depSelectId,
            provSelectId,
            distSelectId,
            sucSelectId,
            depId,
            provId
        );
    });

    $(distSelectId).on("change", function () {
        const distritoId = $(this).val();
        cargarSucursales(distritoId, sucSelectId);
    });
}
