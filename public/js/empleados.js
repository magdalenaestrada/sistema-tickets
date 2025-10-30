$(document).ready(function () {
    const modal = new bootstrap.Modal($('#modalEmpleado')[0]);

    const tabla = $('#tablaEmpleados').DataTable({
        ajax: '/empleados/datatable',
        columns: [
            { data: 'id' },
            { data: 'documento' },
            { data: 'nombre' },
            { data: 'area' },
            { data: 'sucursal' },
            { data: 'cargo' },
            { data: 'acciones', orderable: false, searchable: false }
        ]
    });

    // 🔹 Nuevo empleado
    $('#btnNuevo').click(function () {
        $('#formEmpleado')[0].reset();
        $('#empleado_id').val('');
        $('#seccionUsuario').hide();
        $('#chkUsuario').prop('checked', false);
        modal.show();
    });

    // 🔹 Buscar DNI o RUC
    $('#btnBuscarDocumento').click(function () {
        const documento = $('#documento').val();
        if (!documento) return alert('Ingrese un número de documento.');

        $.ajax({
            url: `/buscar?documento=${documento}`,
            method: 'GET',
            beforeSend: () => $('#btnBuscarDocumento').prop('disabled', true),
            complete: () => $('#btnBuscarDocumento').prop('disabled', false),
            success: function (res) {
                if (res.nombres) {
                    $('#nombres').val(res.nombres);
                    $('#apellidos').val(res.apellido_paterno + ' ' + res.apellido_materno);
                } else if (res.razonSocial) {
                    $('#nombres').val(res.razonSocial);
                    $('#apellidos').val('');
                } else {
                    alert('No se encontró información.');
                }
            },
            error: function () {
                alert('Error al buscar el documento.');
            }
        });
    });

    // 🔹 Mostrar / ocultar campos de usuario
    $('#chkUsuario').change(function () {
        $('#seccionUsuario').toggle(this.checked);
    });

    // 🔹 Guardar empleado
    $('#formEmpleado').submit(function (e) {
        e.preventDefault();
        const datos = $(this).serialize();

        $.ajax({
            url: '/empleados/guardar',
            method: 'POST',
            data: datos,
            success: function (res) {
                if (res.success) {
                    modal.hide();
                    tabla.ajax.reload();
                    alert(res.message);
                } else {
                    alert(res.message);
                }
            },
            error: function (xhr) {
                alert('Error al guardar: ' + xhr.responseText);
            }
        });
    });

    // 🔹 Editar empleado
    $('#tablaEmpleados').on('click', '.editar', function () {
        const id = $(this).data('id');
        $.get(`/empleados/${id}`, function (res) {
            $('#empleado_id').val(res.id);
            $('#documento').val(res.persona.documento);
            $('#nombres').val(res.persona.nombres);
            $('#apellidos').val(res.persona.apellidos);
            $('#correo').val(res.persona.correo);
            $('#telefono').val(res.persona.telefono);
            $('#direccion').val(res.persona.direccion);
            $('#area_id').val(res.area_id);
            $('#sucursal_id').val(res.sucursal_id);
            $('#cargo_id').val(res.cargo_id);

            modal.show();
        });
    });

    // 🔹 Eliminar empleado
    $('#tablaEmpleados').on('click', '.eliminar', function () {
        const id = $(this).data('id');
        if (!confirm('¿Seguro de eliminar este empleado?')) return;

        $.ajax({
            url: `/empleados/${id}`,
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                alert(res.message);
                tabla.ajax.reload();
            },
            error: function () {
                alert('Error al eliminar');
            }
        });
    });
});
