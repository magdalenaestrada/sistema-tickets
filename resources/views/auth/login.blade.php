@extends('layouts.guest')

@section('content')
    <div class="card shadow-lg p-6 sm:p-8 bg-white rounded-lg">
        <h2 class="text-2xl font-bold text-center">Iniciar sesión</h2>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Usuario --}}
            <div class="mb-2">
                <label for="username" class="form-label">Usuario</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="form-control @error('username') is-invalid @enderror">
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" type="password" name="password" required
                    class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- Botón y forgot password --}}
            <div class="flex flex-col sm:flex-row justify-between items-center">
                <button type="submit" class="btn btn-primary">
                    Iniciar sesión
                </button>
            </div>
        </form>
    </div>
@endsection
