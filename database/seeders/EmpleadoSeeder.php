<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empleado::create([
            'persona_id' => 1,
            'sucursal_id' => 1,
            'cargo_id' => 1,
            'fecha_ingreso' => now(),
            'estado' => 'A',
        ]);
    }
}
