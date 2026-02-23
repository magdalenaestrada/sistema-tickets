<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_documento_personas')->insert([
            [
                'codigo' => 'DNI',
                'descripcion' => 'Documento Nacional de Identidad',
                'codigo_sunat' => '1',
                'estado' => 1,
            ],
            [
                'codigo' => 'RUC',
                'descripcion' => 'Registro Único de Contribuyente',
                'codigo_sunat' => '6',
                'estado' => 1,
            ],
            [
                'codigo' => 'CE',
                'descripcion' => 'Carné de Extranjería',
                'codigo_sunat' => '4',
                'estado' => 1,
            ],
            [
                'codigo' => 'PAS',
                'descripcion' => 'Pasaporte',
                'codigo_sunat' => '7',
                'estado' => 1,
            ],
            [
                'codigo' => 'OTR',
                'descripcion' => 'Otro tipo de documento',
                'codigo_sunat' => '0',
                'estado' => 1,
            ],

             [
                'codigo' => 'SIN',
                'descripcion' => 'Sin documento',
                'codigo_sunat' => '9',
                'estado' => 1,
            ],

        ]);
    }
}
