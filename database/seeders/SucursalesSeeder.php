<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        Sucursal::create([
            'empresa_id'      => 1,
            'distrito_id'     => 1,
            'nombre_comercial' => 'Sucursal Lima Central',
            'direccion'       => 'Av. Javier Prado Este 1234',
            'telefono'        => '012345678',
        ]);

        Sucursal::create([
            'empresa_id'      => 1,
            'distrito_id'     => 2,
            'nombre_comercial' => 'Sucursal Arequipa Terminal',
            'direccion'       => 'Terminal Terrestre Arequipa - Oficina 12',
            'telefono'        => '054765432',
        ]);

        Sucursal::create([
            'empresa_id'      => 1,
            'distrito_id'     => 3,
            'nombre_comercial' => 'Sucursal Cusco Centro',
            'direccion'       => 'Av. El Sol 789',
            'telefono'        => '084123456',
        ]);
    }
}
