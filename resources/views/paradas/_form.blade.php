@csrf

<div class="mb-3">

    <label>Descripción</label>

    <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
        value="{{ old('descripcion', $pueblito->descripcion ?? '') }}">

    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

</div>

<div class="mb-3">

    <label>Distrito</label>

    <select name="distrito_id" class="form-control @error('distrito_id') is-invalid @enderror">

        <option value="">Seleccione</option>

        @foreach ($distritos as $distrito)
            <option value="{{ $distrito->id }}" @selected(old('distrito_id', $pueblito->distrito_id ?? '') == $distrito->id)>

                {{ $distrito->nombre }}

            </option>
        @endforeach

    </select>

</div>

<div class="mb-3">

    <label>Sucursal</label>

    <select name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror">

        <option value="">Seleccione</option>

        @foreach ($sucursales as $sucursal)
            <option value="{{ $sucursal->id }}" @selected(old('sucursal_id', $pueblito->sucursal_id ?? '') == $sucursal->id)>

                {{ $sucursal->descripcion }}

            </option>
        @endforeach

    </select>

</div>

<button class="btn btn-success">

    Guardar

</button>

<a href="{{ route('paradas.index') }}" class="btn btn-secondary">

    Cancelar

</a>
