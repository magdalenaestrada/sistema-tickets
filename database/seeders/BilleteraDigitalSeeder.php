<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BilleteraDigitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $now = Carbon::now();

        $billeteras = [
            ['descripcion' => 'Yape'],
            ['descripcion' => 'Plin'],
            ['descripcion' => 'POS'],
        ];

        foreach ($billeteras as $billeta) {
            DB::table('billetas_digitales')->insert([
                'descripcion' => $billeta['descripcion'],
            ]);
        }
    }
}
