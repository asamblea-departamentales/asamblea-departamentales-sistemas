<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Aquí se llama a los seeders 
            RolesAccesoSeeder::class,
            UsuarioSeeder::class,
            ActividadesProgramadasSeeder::class,
            ActividadesRealizadasSeeder::class,
            AtestadosSeeder::class,
            ReportesSeeder::class,
            ReporteActividadSeeder::class,
            BitacoraAdministrativaSeeder::class,
        ]);
    }
}
