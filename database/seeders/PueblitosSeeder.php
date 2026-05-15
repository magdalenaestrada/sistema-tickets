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
                'distrito_id' => 364,
                'descripcion' => 'CAMANA',
            ],
            [
                'distrito_id' => 379,
                'descripcion' => 'CHAPARRA',
            ],
            [
                'distrito_id' => 383,
                'descripcion' => 'QUICACHA',
            ],
            [
                'distrito_id' => 342,
                'descripcion' => 'REPARTICION AYROCA',
            ],
            [
                'distrito_id' => 514,
                'descripcion' => 'CORA CORA',
            ],
        ]);
    }
}
