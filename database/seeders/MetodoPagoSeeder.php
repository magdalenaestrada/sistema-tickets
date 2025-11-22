<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class MetodoPagoSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $metodos = [
            ['descripcion' => 'Efectivo'],
            ['descripcion' => 'Cuenta digital'],
            ['descripcion' => 'Mixto']
        ];

        foreach ($metodos as $metodo) {
            DB::table('metodo_pago')->insert([
                'descripcion' => $metodo['descripcion'],
            ]);
        }
    }
}
