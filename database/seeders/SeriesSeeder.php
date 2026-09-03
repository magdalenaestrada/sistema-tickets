<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SerieSucursal;

class SeriesSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tipos de documento
        |--------------------------------------------------------------------------
        | 1 = Factura
        | 2 = Boleta de venta
        | 3 = Nota de venta
        | 4 = NC BOLETA
        | 5 = NC FACTURA
        */

        $sucursales = [
            // sucursal_id => código de agencia
            1 => '01', // Chala
            2 => '02', // Coracora I
            3 => '04', // Coracora II
            4 => '06', // Pausa
            5 => '09', // Chaparra
            6 => '07', // Camaná
            7 => '08', // Atico
            8 => '05', // Arequipa
            9 => '10', // Incuyo
        ];

        foreach ($sucursales as $sucursalId => $codigo) {

            $series = [
                1 => "FF{$codigo}", // Factura
                2 => "BB{$codigo}", // Boleta
                3 => "NN{$codigo}", // Nota de venta
                4 => "BC{$codigo}", // NC Boleta
                5 => "FC{$codigo}", // NC Factura
            ];

            foreach ($series as $tipoDocumentoId => $serie) {

                SerieSucursal::updateOrCreate(
                    [
                        'sucursal_id' => $sucursalId,
                        'tipo_documento_factura_id' => $tipoDocumentoId,
                    ],
                    [
                        'serie' => $serie,
                    ]
                );
            }
        }
    }
}