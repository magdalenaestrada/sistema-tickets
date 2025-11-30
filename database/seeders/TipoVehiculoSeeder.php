<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoVehiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_vehiculos')->insert([
            [
                'descripcion' => 'Minivan',
                'ruta_svg' => 'vehiculos/minivan.svg',
                'capacidad' => 15,
                'peso_bodega' => 300,
            ],
            [
                'descripcion' => 'Bus',
                'ruta_svg' => 'vehiculos/bus.svg',
                'capacidad' => 46,
                'peso_bodega' => 1500,
            ],
        ]);
    }
}
