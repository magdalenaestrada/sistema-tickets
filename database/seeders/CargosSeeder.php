<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargos')->insert([
            
            ['descripcion' => 'Gerente General'],
            ['descripcion' => 'Administrador'],
            ['descripcion' => 'Conductor'],
            ['descripcion' => 'Despachador Principal'],
            ['descripcion' => 'Atención al Cliente'],
        ]);
    }
}
