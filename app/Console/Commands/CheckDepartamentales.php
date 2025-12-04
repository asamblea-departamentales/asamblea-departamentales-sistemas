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

        // Buscar usuarios con rol superadmin y GOL
        $notificarUsuarios = User::role([
            config('filament-shield.super_admin.name'),
            'GOL'
        ])->get();

        if ($notificarUsuarios->isEmpty()) {
            $this->error('No se encontró ningún usuario con rol superadmin o GOL.');
            return;
        }

        // Obtener todas las departamentales
        $departamentales = DB::table('departamentales')->select('id', 'nombre')->get();

        foreach ($departamentales as $departamental) {
            $count = Actividad::where('departamental_id', $departamental->id)
                              ->whereMonth('fecha', $proximoMes)
                              ->count();

            if ($count === 0) {
                // Notificar a SuperAdmins y GOL
                foreach ($notificarUsuarios as $usuario) {
                    $usuario->notify(new DepartamentalFaltanteNotification(
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

//Modificado para que a los GOL tambien les caiga la notificacion