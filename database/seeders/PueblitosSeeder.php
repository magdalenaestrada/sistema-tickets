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
                'distrito_id' => 335,
                'descripcion' => 'AREQUIPA',
            ],
            [
                'distrito_id' => 378,
                'descripcion' => 'CHALA',
            ],
            [
                'distrito_id' => 514,
                'descripcion' => 'PAUZA',
            ],
        ]);
    }
}
