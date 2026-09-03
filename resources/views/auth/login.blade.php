@extends('layouts.guest')

@section('content')
   <div class="min-vh-100 d-flex align-items-center justify-content-center"
    style="
        background-image: url('{{ asset('storage/fondos/fondo.jpeg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    ">
        {{-- Capa oscura --}}
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.35);">
        </div>
        <div class="card shadow-lg p-4 bg-white rounded-lg position-relative" style="max-width:420px; width:100%; z-index:1;">

            {{-- LOGO --}}
            <div class="text-center mb-4">
                @if ($empresaGlobal && $empresaGlobal->logo)
                    <img src="{{ asset('storage/' . $empresaGlobal->logo) }}" alt="Logo" style="height:70px">
                @else
                    <h4 class="fw-bold">Mi Empresa</h4>
                @endif
            </div>
            {{-- MENSAJE GENERAL DE ERROR --}}
            @if ($errors->has('login'))
                <div class="alert alert-danger">
                    {{ $errors->first('login') }}
                </div>
            @endif

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                        class="form-control @error('username') is-invalid @enderror">

                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>

                    <div class="input-group">
                        <input id="password" type="password" name="password" required
                            class="form-control @error('password') is-invalid @enderror">

                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                            <i data-lucide="eye"></i>
                        </button>

                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                {{-- Botón --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Iniciar sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('password');
        const button = document.getElementById('togglePassword');
        const icon = button.querySelector('i');

        button.addEventListener('click', () => {
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');

            lucide.createIcons();
        });
    });
</script>
