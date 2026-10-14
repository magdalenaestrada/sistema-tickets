<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'descuentos';

    protected $fillable = [
        'codigo',
        'cantidad_usos',
        'fecha_maxima',
        'monto_efectivo',
        'porcentaje',
        'tipo_cupon_id',
        'activo',
        'tipo_asignacion_id',
        'tipo_descuento_id',
    ];

    public function tipo_cupon()
    {
        return $this->belongsTo(TipoCupon::class, 'tipo_cupon_id');
    }

    public function isActivo(): bool
    {
        return $this->activo &&
            $this->tipo_cupon?->estado &&
            (is_null($this->fecha_maxima) || $this->fecha_maxima >= now());
    }

    public function personas()
    {
        return $this->hasMany(DescuentoPersona::class, "descuento_id");
    }

    public function cargos()
    {
        return $this->hasMany(DescuentoCargo::class, "descuento_id");
    }

    public function pasajes()
    {
        return $this->hasMany(Pasaje::class, 'descuento_id');
    }

    // App\Models\Descuento

    public function usosDePersona(int $personaId, ?int $excluirPasajeId = null): int
    {
        return Pasaje::where('descuento_id', $this->id)
            ->where('persona_id', $personaId)
            ->whereIn('estado', ['R', 'V', 'F'])
            ->when($excluirPasajeId, fn($q) => $q->where('id', '!=', $excluirPasajeId))
            ->count();
    }

    public function validarParaPersona(
        ?Persona $persona,
        int $usosEnEstaVenta = 0,
        ?int $excluirPasajeId = null
    ): ?string {
        if (!$this->isActivo()) {
            return 'Descuento inactivo o vencido';
        }

        if ($this->tipo_asignacion_id !== 'T') {
            if (!$persona) {
                return 'Debe indicar el documento del cliente';
            }

            $esCliente = Cliente::where('persona_id', $persona->id)->exists();
            $cargoId   = Empleado::where('persona_id', $persona->id)->value('cargo_id');

            $asignado =
                $this->personas()->where('persona_id', $persona->id)->exists()
                || ($cargoId && $this->cargos()->where('cargo_id', $cargoId)->exists())
                || ($this->tipo_asignacion_id === 'C' && $esCliente);

            if (!$asignado) {
                return 'Este cupón no está asignado a esta persona';
            }
        }

        if ($persona && !is_null($this->cantidad_usos)) {
            $usados = $this->usosDePersona($persona->id, $excluirPasajeId) + $usosEnEstaVenta;

            if ($usados >= $this->cantidad_usos) {
                return 'El cupón ya alcanzó su límite de usos para este pasajero';
            }
        }

        return null;
    }
}
