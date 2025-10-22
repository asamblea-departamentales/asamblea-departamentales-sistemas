<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Actividad;
use App\Models\User;
use App\Notifications\DepartamentalFaltanteNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckDepartamentales extends Command
{
    protected $signature = 'departamentales:check';
    protected $description = 'Verifica si todas las departamentales tienen actividades programadas para el próximo mes';

    public function handle()
    {
        $proximoMes = Carbon::now()->addMonth()->month;

        // Buscar todos los usuarios con rol superadmin (usando Spatie + Filament Shield)
        $superadmins = User::role(config('filament-shield.super_admin.name'))->get();

        if ($superadmins->isEmpty()) {
            $this->error('No se encontró ningún usuario con rol superadmin.');
            return;
        }

        // Obtener todas las departamentales
        $departamentales = DB::table('departamentales')->select('id', 'nombre')->get();

        foreach ($departamentales as $departamental) {
            $count = Actividad::where('departamental_id', $departamental->id)
                              ->whereMonth('fecha', $proximoMes)
                              ->count();

            if ($count === 0) {
                foreach ($superadmins as $superadmin) {
                    $superadmin->notify(new DepartamentalFaltanteNotification(
                        $departamental->id,
                        $departamental->nombre
                    ));
                }

                $this->info("Notificación enviada por falta de actividades: {$departamental->nombre}");
            }
        }

        $this->info('Revisión completada.');
    }
}
