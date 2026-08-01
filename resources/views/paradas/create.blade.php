@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card">

        <div class="card-header">

            Nuevo Pueblito

        </div>

        <div class="card-body">

            <form action="{{ route('paradas.store') }}"
                  method="POST">

                @include('paradas._form')

            </form>

        </div>

    </div>

</div>

@endsection