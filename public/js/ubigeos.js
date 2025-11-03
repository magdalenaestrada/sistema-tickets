$(document).ready(function () {
    // Cargar departamentos al iniciar
    $.get('/ubigeos/departamentos', function (departamentos) {
        let $select = $('#departamento_id');
        $select.empty().append('<option value="">Seleccione</option>');
        departamentos.forEach(d => $select.append(`<option value="${d.id}">${d.nombre}</option>`));
    });

    // Al cambiar departamento → cargar provincias
    $('#departamento_id').change(function () {
        let id = $(this).val();
        $('#provincia_id').empty().append('<option value="">Seleccione</option>');
        $('#distrito_id').empty().append('<option value="">Seleccione</option>');
        if (!id) return;

        $.get(`/ubigeos/provincias/${id}`, function (provincias) {
            provincias.forEach(p => $('#provincia_id').append(`<option value="${p.id}">${p.nombre}</option>`));
        });
    });

    // Al cambiar provincia → cargar distritos
    $('#provincia_id').change(function () {
        let id = $(this).val();
        $('#distrito_id').empty().append('<option value="">Seleccione</option>');
        if (!id) return;

        $.get(`/ubigeos/distritos/${id}`, function (distritos) {
            distritos.forEach(d => $('#distrito_id').append(`<option value="${d.id}">${d.nombre}</option>`));
        });
    });
});

