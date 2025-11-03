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
                'descripcion' => 'Minivan 5 personas',
                'imagen' => '<svg width="64" height="64" xmlns="http://www.w3.org/2000/svg"><rect width="64" height="32" y="16" fill="#ccc"/><circle cx="16" cy="48" r="8" fill="#000"/><circle cx="48" cy="48" r="8" fill="#000"/></svg>',
                'capacidad' => 7,
                'peso_bodega' => 300, // en kg
            ],
            [
                'descripcion' => 'Bus mediano 30 pasajeros',
                'imagen' => '<svg width="64" height="64" xmlns="http://www.w3.org/2000/svg"><rect width="64" height="40" y="12" fill="#999"/><circle cx="16" cy="56" r="8" fill="#000"/><circle cx="48" cy="56" r="8" fill="#000"/></svg>',
                'capacidad' => 30,
                'peso_bodega' => 1500, // en kg
            ],
            [
                'descripcion' => 'Bus grande 50 pasajeros',
                'imagen' => '<svg width="64" height="64" xmlns="http://www.w3.org/2000/svg"><rect width="64" height="48" y="8" fill="#666"/><circle cx="16" cy="56" r="8" fill="#000"/><circle cx="48" cy="56" r="8" fill="#000"/></svg>',
                'capacidad' => 50,
                'peso_bodega' => 2500, // en kg
            ],
        ]);
    }
}
