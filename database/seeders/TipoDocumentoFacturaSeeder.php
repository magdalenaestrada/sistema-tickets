<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoDocumentoFacturaSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $documentos = [
            [
                'codigo' => '01',
                'descripcion' => 'Factura',
                'codigo_sunat' => '1',
                'estado' => 'A',
            ],
            [
                'codigo' => '03',
                'descripcion' => 'Boleta de venta',
                'codigo_sunat' => '2',
                'estado' => 'A',
            ],
        ];

        foreach ($documentos as $doc) {
            DB::table('tipo_documentos_factura')->insert([
                'codigo' => $doc['codigo'],
                'descripcion' => $doc['descripcion'],
                'codigo_sunat' => $doc['codigo_sunat'],
                'estado' => $doc['estado'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
