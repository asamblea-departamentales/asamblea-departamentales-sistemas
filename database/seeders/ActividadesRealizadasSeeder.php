<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadesRealizadasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('actividades_realizadas')->insert([
            [
                'actividad_programada_id' => 1,
                'usuario_id' => 1,
                'fecha' => '2025-01-22',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_EDUCACION_CIVICA',
                'macroactividad' => 'JORNADAS LEGISLATIVAS EN INSTITUCIONES EDUCATIVAS Y ENTIDADES GUBERNAMENTALES Y NO GUBERNAMENTALES',
                'actividad' => 'JORNADA LEGISLATIVA',
                'hora' => '09:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'asistencia' => 30,
                'hombres' => 18,
                'mujeres' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_programada_id' => 2,
                'usuario_id' => 2,
                'fecha' => '2025-01-13',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_PARTICIPACION_CIUDADANA',
                'macroactividad' => 'DESARROLLO DE MESAS TEMÁTICAS DE JUVENTUD, MEDIOAMBIENTE Y PREVENCIÓN DE LA VIOLENCIA CONTRA LA MUJER',
                'actividad' => 'MESA TEMATICA',
                'hora' => '14:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'asistencia' => 25,
                'hombres' => 10,
                'mujeres' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_programada_id' => 3,
                'usuario_id' => 2,
                'fecha' => '2025-01-17',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_PARTICIPACION_CIUDADANA',
                'macroactividad' => 'CAPACITACIONES DE GESTIÓN PUBLICA Y DESARROLLO LOCAL SOSTENIBLE',
                'actividad' => 'CAPACITACIONES DE GESTION PUBLICA',
                'hora' => '10:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'asistencia' => 15,
                'hombres' => 8,
                'mujeres' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_programada_id' => 4,
                'usuario_id' => 1,
                'fecha' => '2025-01-27',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_ATENCION_CIUDADANA',
                'macroactividad' => 'ESPACIOS O IMPRESOS DE DIVULGACIÓN DE NORMATIVAS APROBADAS',
                'actividad' => 'DIVULGACION DE NORMATIVAS APROBADAS',
                'hora' => '16:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'asistencia' => 40,
                'hombres' => 20,
                'mujeres' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'actividad_programada_id' => 5,
                'usuario_id' => 2,
                'fecha' => '2025-01-29',
                'mes' => 'Enero',
                'programa' => 'PROGRAMA_DE_ATENCION_CIUDADANA',
                'macroactividad' => 'ESPACIOS O IMPRESOS DE DIVULGACIÓN DE NORMATIVAS APROBADAS',
                'actividad' => 'DIVULGACION DE NORMATIVAS APROBADAS',
                'hora' => '11:00:00',
                'departamental' => 'OFICINA DEPARTAMENTAL DE AHUACHAPAN',
                'lugar' => 'LIDERES COMUNALES',
                'asistencia' => 35,
                'hombres' => 17,
                'mujeres' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
