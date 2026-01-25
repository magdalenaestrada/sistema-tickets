<?php

namespace Database\Seeders;

use App\Models\TipoViaje;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoViajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoViaje::insert([
            [
                "descripcion" => "Directo",

            ],

            [
                "descripcion" => "Con Tramos",

            ]
        ]);
    }
}
