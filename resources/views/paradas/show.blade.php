@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card">

        <div class="card-header">

            Detalle

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>

                    <th>ID</th>

                    <td>{{ $pueblito->id }}</td>

                </tr>

                <tr>

                    <th>Descripción</th>

                    <td>{{ $pueblito->descripcion }}</td>

                </tr>

                <tr>

                    <th>Distrito</th>

                    <td>{{ $pueblito->distrito->nombre }}</td>

                </tr>

                <tr>

                    <th>Sucursal</th>

                    <td>{{ $pueblito->sucursal->descripcion }}</td>

                </tr>

            </table>

            <a href="{{ route('paradas.index') }}"
               class="btn btn-secondary">

                Volver

            </a>

        </div>

    </div>

</div>

@endsection