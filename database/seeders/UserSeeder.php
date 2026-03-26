<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['documento' => '71043591'],
            [
                'persona_id'        => 1,
                'sucursal_id'       => 1,
                'numero_licencia'   => null,
                'username'          => 'superadministrador@edimsa.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('12345678'),
                'estado'            => 'A',
                'fecha_creacion'    => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $user->assignRole('Administrador');
    }
}
