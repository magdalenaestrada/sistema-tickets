<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'documento'              => '20123456789',
            'razon_social'           => 'Transporte Sol del Perú S.A.C.',
            'nombre_comercial'       => 'Sol del Perú',
            'direccion'              => 'Av. Aviación 1234, Lima',
            'usuario_facturacion'    => 'FACT01',
            'contrasena_facturacion' => 'claveSecreta',
            'estado'                 => 'A',
        ]);

        Empresa::create([
            'documento'              => '20456789123',
            'razon_social'           => 'Encomiendas del Sur S.A.',
            'nombre_comercial'       => 'EDS Cargo',
            'direccion'              => 'Av. Independencia 456, Arequipa',
            'usuario_facturacion'    => null,
            'contrasena_facturacion' => null,
            'estado'                 => 'A',
        ]);
    }
}
