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
                'descripcion' => 'ACHANIZO',
            ],
            [
                'distrito_id' => 378,
                'descripcion' => 'TIRUQUE',
            ],
            [
                'distrito_id' => 378,
                'descripcion' => 'MOLINO',
            ],
            [
                'distrito_id' => 379,
                'descripcion' => 'ARASQUI',
            ],
            [
                'distrito_id' => 383,
                'descripcion' => 'TIERRAS BLANCAS',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'LA VICTORIA',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'SIFUENTES',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'CRUCE SONDOR',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'CRUCE CAHUACHO',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'CRUCE AYROCA',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'SALLA SALLA',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'INCUYO',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'COLLONI',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'YURACCHUASI',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'UNTUCO',
            ],
            [
                'distrito_id' => 377,
                'descripcion' => 'CRUCE TARCO',
            ],
            [
                'distrito_id' => 372,
                'descripcion' => 'INCAHUASI',
            ],
            [
                'distrito_id' => 372,
                'descripcion' => 'CARHUANILLA',
            ],
            [
                'distrito_id' => 372,
                'descripcion' => 'CHUMPI',
            ],
            [
                'distrito_id' => 353,
                'descripcion' => 'QUILCATA',
            ],

            [
                'distrito_id' => 355,
                'descripcion' => 'EL ALTO',
            ],

            [
                'distrito_id' => 364,
                'descripcion' => 'ATICO',
            ],

        ]);
    }
}
