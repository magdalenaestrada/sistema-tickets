<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_servicio')->insert([
            [
                'descripcion' => 'Boleto',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Encomienda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Equipaje Extra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
