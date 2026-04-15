<?php

namespace Database\Seeders;

use App\Models\SerieSucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SerieSucursal::insert([
            [
                "descripcion" => "BBB1 / FFF1 / NNN1",
                "codigo" => "001"
            ],
            [
                "descripcion" => "BBB2 / FFF2 / NNN2",
                "codigo" => "002"
            ],
            [
                "descripcion" => "BBB3 / FFF3 / NNN3",
                "codigo" => "003"
            ],
            [
                "descripcion" => "BBB4 / FFF4 / NNN4",
                "codigo" => "004"
            ],
            [
                "descripcion" => "BBB5 / FFF5 / NNN5",
                "codigo" => "005"
            ],
            [
                "descripcion" => "BBB6 / FFF6 / NNN6",
                "codigo" => "006"
            ],
            [
                "descripcion" => "BBB7 / FFF7 / NNN7",
                "codigo" => "007"
            ],
            [
                "descripcion" => "BBB8 / FFF8 / NNN8",
                "codigo" => "008"
            ],
            [
                "descripcion" => "BBB9 / FFF9 / NNN9",
                "codigo" => "009"
            ],
            [
                "descripcion" => "BB10 / FF10 / NN10",
                "codigo" => "010"
            ],
        ]);
    }
}
