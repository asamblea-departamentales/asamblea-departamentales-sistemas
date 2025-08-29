<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reportes')->insert([
            [
                'usuario_id' => 4, // Analista
                'tipo' => 'Mensual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-01-31',
                'descripcion' => json_encode([
                    'resumen' => 'Reporte de enero 2025',
                    'actividades' => 5,
                    'asistencia_total' => 145,
                    'hombres' => 73,
                    'mujeres' => 72,
                    'departamentales' => [
                        'OFICINA DEPARTAMENTAL DE AHUACHAPAN' => 145,
                    ],
                ]),
                'estado' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 4, // Analista
                'tipo' => 'Anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'descripcion' => json_encode([
                    'resumen' => 'Reporte anual de actividades 2025',
                    'actividades' => 5,
                    'asistencia_total' => 145,
                    'hombres' => 73,
                    'mujeres' => 72,
                ]),
                'estado' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
