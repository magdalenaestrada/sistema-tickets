window.cargarSelectGeneral = function (modelos, callback = null) {
    $.get('/listas', function (res) {
        modelos.forEach((item) => {
            const select = $('#' + item.id);
            const data = res[item.modelo];

            select.empty().append('<option value=""></option>');

            if (data && data.length > 0) {
                data.forEach(d => {
                    select.append(`<option value="${d.id}">${d.nombre}</option>`);
                });
            }

            // Refrescar si usa Select2
            if (select.hasClass('select2-hidden-accessible')) {
                select.trigger('change.select2');
            }
        });

        if (callback) callback();
    }).fail(() => {
        alert('Error al cargar listas');
    });
};
