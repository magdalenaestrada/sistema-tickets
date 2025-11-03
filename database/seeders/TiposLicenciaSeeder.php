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
            ['codigo' => 'AI',   'descripcion' => 'AI - Licencia de Clase AI'],
            ['codigo' => 'AIIa', 'descripcion' => 'AIIa - Licencia de Clase AIIa'],
            ['codigo' => 'AIIb', 'descripcion' => 'AIIb - Licencia de Clase AIIb'],
            ['codigo' => 'AIIIa', 'descripcion' => 'AIIIa - Licencia de Clase AIIIa'],
            ['codigo' => 'AIIIb', 'descripcion' => 'AIIIb - Licencia de Clase AIIIb'],
            ['codigo' => 'BI',   'descripcion' => 'BI - Licencia de Clase BI'],
            ['codigo' => 'BIIa', 'descripcion' => 'BIIa - Licencia de Clase BIIa'],
            ['codigo' => 'BIIb', 'descripcion' => 'BIIb - Licencia de Clase BIIb'],
        ];

        DB::table('tipo_licencias')->insert($licencias);
    }
}
