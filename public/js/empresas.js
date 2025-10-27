$(function () {
    const tabla = $('#tablaEmpresas').DataTable({
        ajax: '/empresas/datatable',
        columns: [
            { title: 'Documento', data: 'documento' },
            { title: 'Razón Social', data: 'razon_social' },
            { title: 'Nombre Comercial', data: 'nombre_comercial' },
            { title: 'Dirección', data: 'direccion' },
            { title: 'Estado', data: 'estado' },
            { title: 'Opciones', data: 'opciones', orderable: false, searchable: false },
        ],
    });

    $('#btnNuevaEmpresa').on('click', () => {
        $('#formEmpresa')[0].reset();
        $('#empresa_id').val('');
        $('#modalEmpresa').modal('show');
    });

    $('#formEmpresa').on('submit', async function (e) {
        e.preventDefault();
        const form = this;
        const id = $('#empresa_id').val();
        const url = id ? `/empresas/${id}` : '/empresas';
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                body: new FormData(form),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            });
            const data = await res.json();

            if (res.ok) {
                Swal.fire('Éxito', data.message ?? 'Guardado correctamente', 'success');
                $('#modalEmpresa').modal('hide');
                tabla.ajax.reload();
            } else {
                Swal.fire('Error', data.error ?? 'No se pudo guardar', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
        }
    });

    $(document).on('click', '.eliminarEmpresa', function () {
        const url = $(this).data('action');
        Swal.fire({
            title: '¿Eliminar empresa?',
            text: 'Esta acción no elimina los datos físicamente (Soft Delete).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                });
                const data = await res.json();
                if (res.ok) {
                    Swal.fire('Eliminado', data.message, 'success');
                    tabla.ajax.reload();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            }
        });
    });

    $(document).on('click', '.editarEmpresa', async function () {
        const id = $(this).data('id');
        const res = await fetch(`/empresas/${id}`);
        const data = await res.json();

        if (res.ok) {
            $('#empresa_id').val(data.id);
            for (const [key, value] of Object.entries(data)) {
                $(`[name="${key}"]`).val(value);
            }
            $('#modalEmpresa').modal('show');
        } else {
            Swal.fire('Error', data.error ?? 'No se pudo cargar la empresa', 'error');
        }
    });

    $('#btnNuevaSucursal').on('click', async () => {
        const res = await fetch('/empresas/list');
        const data = await res.json();
        const $select = $('#formSucursal select[name="empresa_id"]');
        $select.empty();
        data.forEach(e => $select.append(`<option value="${e.id}">${e.razon_social}</option>`));
        $('#modalSucursal').modal('show');
    });

    $('#formSucursal').on('submit', async function (e) {
        e.preventDefault();
        const form = this;

        const res = await fetch('/sucursales', {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        });
        const data = await res.json();
        if (res.ok) {
            Swal.fire('Éxito', data.message ?? 'Sucursal registrada', 'success');
            $('#modalSucursal').modal('hide');
            form.reset();
        } else {
            Swal.fire('Error', data.error ?? 'No se pudo registrar', 'error');
        }
    });
    
});
