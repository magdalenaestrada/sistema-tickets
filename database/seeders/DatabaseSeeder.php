<?php

namespace Database\Seeders;

use App\Models\TipoLicencia;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaisSeeder::class,
            DepartamentosSeeder::class,
            ProvinciasSeeder::class,
            DistritosSeeder::class,
            RolesSeeder::class,
            AreasSeeder::class,
            CargosSeeder::class,
            TiposLicenciaSeeder::class,
            TipoDocumentoSeeder::class,
            TipoVehiculoSeeder::class,
            TipoViajeSeeder::class,
            TipoEventoSeeder::class,
        ]);
    }
}
