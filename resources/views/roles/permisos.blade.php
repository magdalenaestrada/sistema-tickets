@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asignar permisos al rol: {{ $rol->name }}</h5>
        </div>

        <div class="card-body">
            <form id="form-permisos">
                @csrf
                <input type="hidden" name="rol_id" value="{{ $rol->id }}">

                <div class="form-group">
                    @foreach ($permisos as $permiso)
                        @php
                            $checkboxId = 'permiso_' . $permiso->id;
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                id="{{ $checkboxId }}" {{ in_array($permiso->id, $rolPermisosIds) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $checkboxId }}">{{ $permiso->name }}</label>
                        </div>
                    @endforeach
                </div>
                <br>
                <button type="submit" class="btn btn-primary">Guardar permisos</button>
                <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#form-permisos').submit(function(e) {
                e.preventDefault();

                let url = "{{ route('roles.guardarPermisos') }}";

                $.post(url, $(this).serialize(), function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message
                    }).then(() => {
                        window.location.href = "{{ route('roles.index') }}";
                    });
                }).fail(function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ??
                            'Ocurrió un error al guardar permisos'
                    });
                });
            });

            $('#btn-cancelar').click(function() {
                Swal.fire({
                    title: '¿Cancelar?',
                    text: "No se guardarán los cambios.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('roles.index') }}";
                    }
                });
            });
        });
    </script>
@endpush
