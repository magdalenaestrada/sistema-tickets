@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Gestión de Usuarios</h5>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="filtroEmpleado" class="form-control" placeholder="Buscar por nombre">
                </div>

                <div class="col-md-4">
                    <input type="text" id="filtroUsuario" class="form-control" placeholder="Buscar por username">
                </div>
            </div>

            <table id="tablaUsuarios" class="table table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empleado</th>
                        <th>Username</th>
                        <th width="80">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('users.modals.edit')
@endsection

@push('scripts')
    <script src="{{ asset('js/users.js') }}"></script>
@endpush
