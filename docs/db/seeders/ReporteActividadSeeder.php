<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReporteActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reporte_actividad')->insert([
            ['reporte_id' => 1, 'actividad_id' => 1],
            ['reporte_id' => 1, 'actividad_id' => 2],
            ['reporte_id' => 1, 'actividad_id' => 3],
            ['reporte_id' => 1, 'actividad_id' => 4],
            ['reporte_id' => 2, 'actividad_id' => 5],
        ]);
    }
}
