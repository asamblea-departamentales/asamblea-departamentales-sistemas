<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAccesoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles_acceso')->insert([
            ['nombre' => 'Coordinador Departamental', 'descripcion' => 'Control total de los modulos de su oficina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Asistente Tecnico', 'descripcion' => 'Modulos operativos y administrativos de su oficina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gerencia de Operaciones', 'descripcion' => 'Acceso consolidado a todas las oficinas y al sistema entero', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Administracion Tecnica', 'descripcion' => 'Vista completa, configuracion de catalogos, usuarios y mantenimiento', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Auditoria Interna', 'descripcion' => 'Acceso a registros cerrados y trazabilidad historica', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
