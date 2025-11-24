<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEncomiendaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_encomienda')->insert([
            [
                'descripcion'       => 'Sobre',
                'precio_base'       => 5.00,
                'peso_limite'       => 0.50,
                'costo_kilo_extra'  => 10.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'descripcion'       => 'Caja Pequeña',
                'precio_base'       => 10.00,
                'peso_limite'       => 2.00,
                'costo_kilo_extra'  => 8.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'descripcion'       => 'Caja Mediana',
                'precio_base'       => 20.00,
                'peso_limite'       => 5.00,
                'costo_kilo_extra'  => 6.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'descripcion'       => 'Bulto Grande',
                'precio_base'       => 30.00,
                'peso_limite'       => 8.00,
                'costo_kilo_extra'  => 5.00,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
