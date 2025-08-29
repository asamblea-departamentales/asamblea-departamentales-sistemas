<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadesProgramadasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('actividades_programadas_')->insert([
            [
                'usuario_id' => 1,
                'fecha' => '2025-01-22',
                'dia' => 'Lunes',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_EDUCACION_CIVICA',
                'macroactividad' => 'JORNADAS LEGISLATIVAS EN INSTITUCIONES EDUCATIVAS Y ENTIDADES GUBERNAMENTALES Y NO GUBERNAMENTALES',
                'actividad' => 'JORNADA LEGISLATIVA',
                'hora' => '09:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'fecha' => '2025-01-13',
                'dia' => 'Viernes',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_PARTICIPACION_CIUDADANA',
                'macroactividad' => 'DESARROLLO DE MESAS TEMÁTICAS DE JUVENTUD, MEDIOAMBIENTE Y PREVENCIÓN DE LA VIOLENCIA CONTRA LA MUJER',
                'actividad' => 'MESA TEMATICA',
                'hora' => '14:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'fecha' => '2025-01-17',
                'dia' => 'Miércoles',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_PARTICIPACION_CIUDADANA',
                'macroactividad' => 'CAPACITACIONES DE  GESTIÓN PUBLICA Y DESARROLLO LOCAL SOSTENIBLE',
                'actividad' => 'CAPACITACIONES DE GESTION PUBLICA',
                'hora' => '10:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'fecha' => '2025-01-27',
                'dia' => 'Martes',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_ATENCION_CIUDADANA',
                'macroactividad' => 'ESPACIOS O IMPRESOS DE DIVULGACIÓN DE NORMATIVAS APROBADAS',
                'actividad' => 'DIVULGACION DE NORMATIVAS APROBADAS',
                'hora' => '16:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'fecha' => '2025-01-29',
                'dia' => 'Domingo',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_ATENCION_CIUDADANA',
                'macroactividad' => 'ESPACIOS O IMPRESOS DE DIVULGACIÓN DE NORMATIVAS APROBADAS',
                'actividad' => 'DIVULGACION DE NORMATIVAS APROBADAS',
                'hora' => '11:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
