<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiposLicenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licencias = [
            ['codigo' => 'AIIc', 'descripcion' => 'AIIc - Licencia de Clase AIIc'],
        ];

        DB::table('tipo_licencias')->insert($licencias);
    }
}
