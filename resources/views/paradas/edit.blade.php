@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card">

        <div class="card-header">

            Editar Pueblito

        </div>

        <div class="card-body">

            <form action="{{ route('paradas.update',$pueblito) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('paradas._form')

            </form>

        </div>

    </div>

</div>

@endsection