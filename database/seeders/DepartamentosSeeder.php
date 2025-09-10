<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PERU
        DB::table("departamentos")->insert([
            ['id' => '1', 'pais_id' => 1, 'ubigeo' => '01', 'nombre' => 'Amazonas'],
            ['id' => '2', 'pais_id' => 1, 'ubigeo' => '02', 'nombre' => 'Áncash'],
            ['id' => '3', 'pais_id' => 1, 'ubigeo' => '03', 'nombre' => 'Apurímac'],
            ['id' => '4', 'pais_id' => 1, 'ubigeo' => '04', 'nombre' => 'Arequipa'],
            ['id' => '5', 'pais_id' => 1, 'ubigeo' => '05', 'nombre' => 'Ayacucho'],
            ['id' => '6', 'pais_id' => 1, 'ubigeo' => '06', 'nombre' => 'Cajamarca'],
            ['id' => '7', 'pais_id' => 1, 'ubigeo' => '07', 'nombre' => 'Callao'],
            ['id' => '8', 'pais_id' => 1, 'ubigeo' => '08', 'nombre' => 'Cusco'],
            ['id' => '9', 'pais_id' => 1, 'ubigeo' => '09', 'nombre' => 'Huancavelica'],
            ['id' => '10', 'pais_id' => 1, 'ubigeo' => '10', 'nombre' => 'Huánuco'],
            ['id' => '11', 'pais_id' => 1, 'ubigeo' => '11', 'nombre' => 'Ica'],
            ['id' => '12', 'pais_id' => 1, 'ubigeo' => '12', 'nombre' => 'Junín'],
            ['id' => '13', 'pais_id' => 1, 'ubigeo' => '13', 'nombre' => 'La Libertad'],
            ['id' => '14', 'pais_id' => 1, 'ubigeo' => '14', 'nombre' => 'Lambayeque'],
            ['id' => '15', 'pais_id' => 1, 'ubigeo' => '15', 'nombre' => 'Lima'],
            ['id' => '16', 'pais_id' => 1, 'ubigeo' => '16', 'nombre' => 'Loreto'],
            ['id' => '17', 'pais_id' => 1, 'ubigeo' => '17', 'nombre' => 'Madre de Dios'],
            ['id' => '18', 'pais_id' => 1, 'ubigeo' => '18', 'nombre' => 'Moquegua'],
            ['id' => '19', 'pais_id' => 1, 'ubigeo' => '19', 'nombre' => 'Pasco'],
            ['id' => '20', 'pais_id' => 1, 'ubigeo' => '20', 'nombre' => 'Piura'],
            ['id' => '21', 'pais_id' => 1, 'ubigeo' => '21', 'nombre' => 'Puno'],
            ['id' => '22', 'pais_id' => 1, 'ubigeo' => '22', 'nombre' => 'San Martín'],
            ['id' => '23', 'pais_id' => 1, 'ubigeo' => '23', 'nombre' => 'Tacna'],
            ['id' => '24', 'pais_id' => 1, 'ubigeo' => '24', 'nombre' => 'Tumbes'],
            ['id' => '25', 'pais_id' => 1, 'ubigeo' => '25', 'nombre' => 'Ucayali']
        ]);
    }
}
