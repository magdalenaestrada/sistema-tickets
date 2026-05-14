<?php

namespace Database\Seeders;

use App\Models\Pueblito;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PueblitosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pueblito::insert([
            [
                'distrito_id' => 378,
                'nombre' => 'CHALA',
            ],
            [
                'distrito_id' => 378,
                'nombre' => 'ACHANIZO',
            ],
            [
                'distrito_id' => 378,
                'nombre' => 'TIRUQUE',
            ],
            [
                'distrito_id' => 378,
                'nombre' => 'MOLINO',
            ],

            [
                'distrito_id' => 379,
                'nombre' => 'CHAPARRA',
            ],
            [
                'distrito_id' => 379,
                'nombre' => 'ARASQUI',
            ],
            [
                'distrito_id' => 379,
                'nombre' => 'SIFUENTES',
            ],
            [
                "distrito_id" => 383,
                "nombre" => 'QUICACHA',
            ],
            [
                "distrito_id" => 383,
                "nombre" => 'TIERRAS BLANCAS',
            ]
                            'LA VICTORIA',
                'CRUCE SONDOR',
                'CRUCE CAHUACHO',
                'CRUCE AYROCA',
                'CRUCE TARCO',

        ]);
    }
}
