
$(document).ready(function () {
    // Cargar departamentos al iniciar
    $.get(route('ubigeos.departamentos'), function (departamentos) {
        const $select = $('#departamento_id');
        $select.empty().append('<option value="">Seleccione</option>');
        departamentos.forEach(d => $select.append(`<option value="${d.id}">${d.nombre}</option>`));
    });

    // Al cambiar departamento → cargar provincias
    $('#departamento_id').change(function () {
        const id = $(this).val();
        $('#provincia_id').empty().append('<option value="">Seleccione</option>');
        $('#distrito_id').empty().append('<option value="">Seleccione</option>');
        if (!id) return;

        $.get(route('ubigeos.provincias', id), function (provincias) {
            provincias.forEach(p => $('#provincia_id').append(`<option value="${p.id}">${p.nombre}</option>`));
        });
    });

    // Al cambiar provincia → cargar distritos
    $('#provincia_id').change(function () {
        const id = $(this).val();
        $('#distrito_id').empty().append('<option value="">Seleccione</option>');
        if (!id) return;

        $.get(route('ubigeos.distritos', id), function (distritos) {
            distritos.forEach(d => $('#distrito_id').append(`<option value="${d.id}">${d.nombre}</option>`));
        });
    });
});
