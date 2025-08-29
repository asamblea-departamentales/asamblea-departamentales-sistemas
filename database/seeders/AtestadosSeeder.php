<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtestadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('atestados')->insert([
            [
                'actividad_realizada_id' => 1,
                'descripcion' => 'Fotos de la jornada legislativa',
                'archivo' => 'fotos_jornada_2025_01_22.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_realizada_id' => 1,
                'descripcion' => 'Certificados de participación',
                'archivo' => 'certificados_jornada_2025_01_22.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_realizada_id' => 2,
                'descripcion' => 'Acta de la mesa temática',
                'archivo' => 'acta_mesa_2025_01_13.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_realizada_id' => 3,
                'descripcion' => 'Material de capacitación',
                'archivo' => 'material_capacitacion_2025_01_17.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_realizada_id' => 4,
                'descripcion' => 'Folleto de normativas',
                'archivo' => 'folleto_normativas_2025_01_27.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
