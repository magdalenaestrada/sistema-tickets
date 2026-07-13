<?php

namespace Database\Seeders;

use App\Models\GrupoSerie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrupoSerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 9; $i++) {

            GrupoSerie::updateOrCreate(
                ['codigo' => str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'descripcion' => 'Grupo ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'estado' => 'A',
                ]
            );
        }
    }
}
