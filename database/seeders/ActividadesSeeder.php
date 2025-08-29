<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActividadesSeeder extends Seeder
{
    public function run(): void
    {
        $departamentales = DB::table('departamentales')->pluck('id'); // IDs de departamentales
        $users = DB::table('users')->pluck('id'); // UUIDs de usuarios

        $nombresActividades = [
            ['Programa de Salud Comunitaria', 'Jornada médica', 'Centro de salud'],
            ['Capacitación TICs', 'Formación en tecnología', 'Escuela local'],
            ['Foro de Liderazgo Juvenil', 'Liderazgo y participación', 'Casa comunal'],
            ['Campaña de Medio Ambiente', 'Reforestación y limpieza', 'Parque central'],
            ['Feria de Emprendimiento', 'Apoyo a emprendedores', 'Plaza departamental'],
        ];

        foreach ($departamentales as $departamentalId) {
            foreach ($nombresActividades as $actividad) {
                DB::table('actividades')->insert([
                    'user_id' => $users->random(), // UUID existente
                    'fecha' => Carbon::now()->subDays(rand(1, 30)),
                    'departamental_id' => $departamentalId,
                    'programa' => $actividad[0],
                    'macroactividad' => $actividad[1],
                    'lugar' => $actividad[2],
                    'asistentes_hombres' => rand(5, 50),
                    'asistentes_mujeres' => rand(5, 50),
                    'asistencia_completa' => rand(10, 100),
                    'estado' => collect(['Pendiente', 'En Progreso', 'Completada'])->random(),
                    'star_date' => Carbon::now()->addDays(rand(1, 10)),
                    'due_date' => Carbon::now()->addDays(rand(11, 20)),
                    'reminder_at' => Carbon::now()->addDays(rand(1, 5)),
                    'atestados' => json_encode([
                        'evidencia' => 'foto_'.Str::random(5).'.jpg',
                        'informe' => 'informe_'.Str::random(5).'.pdf',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
