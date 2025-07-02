<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BitacoraAdministrativaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bitacoras_administrativas')->insert([
            [
                'usuario_id' => 1,
                'actividad_id' => 1,
                'accion' => 'Aprobación',
                'descripcion' => 'Aprobada la planificación de la jornada legislativa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'actividad_id' => 2,
                'accion' => 'Actualización',
                'descripcion' => 'Actualizada la fecha de la mesa temática',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'actividad_id' => 3,
                'accion' => 'Inicio',
                'descripcion' => 'Iniciada la capacitación de gestión pública',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'actividad_id' => 4,
                'accion' => 'Confirmación',
                'descripcion' => 'Confirmada la logística de divulgación de normativas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'actividad_id' => 5,
                'accion' => 'Revisión',
                'descripcion' => 'Revisión de la planificación de divulgación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
