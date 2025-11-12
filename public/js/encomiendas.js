$(function () {

    // === Buscar persona por documento ===
    function buscarPersona(tipo) { // 'emisor' o 'receptor'
        let doc = $(`#${tipo}_documento`).val();
        if (!doc) return;

        $.get(`/buscar?documento=${doc}`, function(res) {
            if(res.error){
                alert(res.error);
                return;
            }

            if(res.tipo === 'DNI'){
                $(`#${tipo}_nombres`).val(res.nombres);
                $(`#${tipo}_apellidos`).val(res.apellido_paterno + ' ' + res.apellido_materno);
            } else if(res.tipo === 'RUC'){
                $(`#${tipo}_nombres`).val(res.razon_social);
                $(`#${tipo}_apellidos`).val('');
                $(`#${tipo}_direccion`).val(res.direccion || '');
            }
        }).fail(function(err){
            alert(err.responseJSON?.error || 'Error al buscar documento');
        });
    }

    $('#emisor_documento').on('blur', () => buscarPersona('emisor'));
    $('#receptor_documento').on('blur', () => buscarPersona('receptor'));


    // === Evitar sucursal repetida en origen/destino ===
    $('#origen').on('change', function() {
        let origen = $(this).val();
        $('#destino option').show();
        if(origen) $('#destino option[value="'+origen+'"]').hide();
    });

    $('#destino').on('change', function() {
        let destino = $(this).val();
        $('#origen option').show();
        if(destino) $('#origen option[value="'+destino+'"]').hide();
    });


    // === Tabla de detalles y total ===
    let tabla = $("#tablaEncomiendas").DataTable({
        ajax: '/encomiendas/datatable',
        columns: [
            { data: 'id' },
            { data: 'emisor' },
            { data: 'receptor' },
            { data: 'total' },
            { data: 'estado' },
            { data: 'acciones', orderable: false, searchable: false },
        ]
    });

    $("#btnNueva").click(() => {
        $("#formEncomienda")[0].reset();
        $("#tablaDetalles tbody").empty();
        $("#modalEncomienda").modal('show');
        $('#origen option, #destino option').show();
    });

    $("#btnAgregarDetalle").click(() => {
        $("#tablaDetalles tbody").append(`
            <tr>
                <td><input type="text" class="form-control tipo"></td>
                <td><input type="text" class="form-control desc"></td>
                <td><input type="number" class="form-control peso" step="0.01"></td>
                <td><input type="number" class="form-control costo" step="0.01"></td>
                <td><button type="button" class="btn btn-danger btn-sm btnQuitar">Eliminar</button></td>
            </tr>
        `);
    });

    $(document).on('click', '.btnQuitar', function () {
        $(this).closest('tr').remove();
        recalcularTotal();
    });

    $(document).on('input', '.costo', recalcularTotal);

    function recalcularTotal() {
        let total = 0;
        $(".costo").each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $("#total").val(total.toFixed(2));
    }

    // === Guardar encomienda ===
    $("#formEncomienda").submit(function (e) {
        e.preventDefault();

        let detalles = [];
        $("#tablaDetalles tbody tr").each(function () {
            detalles.push({
                tipo_equipaje: $(this).find(".tipo").val(),
                descripcion: $(this).find(".desc").val(),
                peso: $(this).find(".peso").val(),
                costo: $(this).find(".costo").val(),
            });
        });

        $.ajax({
            url: '/encomiendas/guardar',
            method: 'POST',
            data: {
                _token: $('input[name=_token]').val(),
                emisor: {
                    documento: $("#emisor_documento").val(),
                    nombres: $("#emisor_nombres").val(),
                    apellidos: $("#emisor_apellidos").val(),
                    celular: $("#emisor_celular").val(),
                    direccion: $("#emisor_direccion").val(),
                },
                receptor: {
                    documento: $("#receptor_documento").val(),
                    nombres: $("#receptor_nombres").val(),
                    apellidos: $("#receptor_apellidos").val(),
                    celular: $("#receptor_celular").val(),
                    direccion: $("#receptor_direccion").val(),
                },
                origen: $("#origen").val(),
                destino: $("#destino").val(),
                total: $("#total").val(),
                detalles: detalles
            },
            success: function (res) {
                if (res.success) {
                    $("#modalEncomienda").modal('hide');
                    tabla.ajax.reload();
                }
            }
        });
    });

});
