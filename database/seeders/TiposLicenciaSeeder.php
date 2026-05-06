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
            ['codigo' => 'AIIIc', 'descripcion' => 'AIIIc - Licencia de Clase AIIIc'],
            ['codigo' => 'BIIIc', 'descripcion' => 'BIIIc - Licencia de Clase BIIIc'],
            ['codigo' => 'AIV', 'descripcion' => 'AIV - Licencia de Clase AIV'],

        ];

        DB::table('tipo_licencias')->insert($licencias);
    }
}
