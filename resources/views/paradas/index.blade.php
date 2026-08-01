@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="card shadow-sm">

        <div class="card-header">
            <h5 class="mb-0">Pueblitos</h5>
        </div>

        <div class="card-body">

            <div id="alertaPueblitos"></div>

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th style="width:20%">Sucursal</th>
                        <th style="width:20%">Distrito</th>
                        <th style="width:30%">Descripción</th>
                        <th style="width:30%">Acciones</th>
                    </tr>
                </thead>

                {{-- Formulario --}}
                <tbody>

                <tr>

                    <td>

                        <select id="sucursal_id" class="form-control">

                            <option value="">Seleccione</option>

                            @foreach($sucursales as $sucursal)

                                <option value="{{ $sucursal->id }}">
                                    {{ $sucursal->nombre_comercial }}
                                </option>

                            @endforeach

                        </select>

                    </td>

                    <td>

                        <select id="distrito_id" class="form-control">

                            <option value="">Seleccione</option>

                            @foreach($distritos as $distrito)

                                <option value="{{ $distrito->id }}">
                                    {{ $distrito->nombre }}
                                </option>

                            @endforeach

                        </select>

                    </td>

                    <td>

                        <input
                            type="text"
                            id="descripcion"
                            class="form-control"
                            placeholder="Descripción">

                    </td>

                    <td>

                        <button
                            class="btn btn-primary w-100"
                            id="btnGuardar">

                            Guardar

                        </button>

                    </td>

                </tr>

                </tbody>

                {{-- Tabla --}}
                <tbody id="tbodyPueblitos">

                @forelse($pueblitos as $item)

                    <tr
                        id="fila-{{ $item->id }}"
                        data-id="{{ $item->id }}"
                        data-sucursal="{{ $item->sucursal_id }}"
                        data-distrito="{{ $item->distrito_id }}"
                        data-descripcion="{{ $item->descripcion }}"
                    >

                        <td class="celdaSucursal">
                            {{ $item->sucursal->nombre_comercial ?? "-"}}
                        </td>

                        <td class="celdaDistrito">
                            {{ $item->distrito->nombre }}
                        </td>

                        <td class="celdaDescripcion">
                            {{ $item->descripcion }}
                        </td>

                        <td class="celdaAcciones">

                            <button
                                class="btn btn-outline-primary btn-sm btnEditar"
                                data-id="{{ $item->id }}">

                                Editar

                            </button>

                            <button
                                class="btn btn-outline-danger btn-sm btnEliminar"
                                data-id="{{ $item->id }}">

                                Eliminar

                            </button>

                        </td>

                    </tr>

                @empty

                    <tr id="filaVacia">

                        <td colspan="4" class="text-center text-muted">

                            No existen registros.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/paradas.js') }}"></script>
@endpush


