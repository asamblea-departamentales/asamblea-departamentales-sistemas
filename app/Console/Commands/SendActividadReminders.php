<?php

namespace App\Console\Commands;

use App\Models\Actividad;
use App\Models\User;
use App\Notifications\ActividadReminderNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendActividadReminders extends Command
{
    protected $signature = 'actividades:reminders {--dry-run : Ejecutar en modo de prueba sin enviar notificaciones}';
    protected $description = 'Enviar notificaciones de recordatorio de actividades programadas';

    public function handle()
    {
        $now = now();
        $isDryRun = $this->option('dry-run');

        $this->info('🔔 SISTEMA DE RECORDATORIOS DE ACTIVIDADES');
        $this->info('==========================================');
        $this->info("⏰ Fecha y hora actual: {$now->format('Y-m-d H:i:s')}");

        if ($isDryRun) {
            $this->warn('🔍 MODO DRY RUN - No se enviarán notificaciones reales');
        }

        $actividades = Actividad::whereNotNull('reminder_at')
            ->where('reminder_at', '<=', $now)
            ->where('estado', '!=', 'Completada')
            ->whereNull('reminder_notified_at')
            ->with('user')
            ->get();

        $this->info("📋 Actividades encontradas: {$actividades->count()}");

        if ($actividades->isEmpty()) {
            $this->info('✅ No hay actividades pendientes de notificación');
            $this->showUpcomingActivities();
            return Command::SUCCESS;
        }

        $notificationsSent = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($actividades as $actividad) {
            try {
                if (!$actividad->user) {
                    $this->warn("⚠️  Actividad #{$actividad->id} '{$actividad->macroactividad}' no tiene usuario asignado - SALTANDO");
                    $skipped++;
                    continue;
                }

                if (!$actividad->star_date) {
                    $this->warn("⚠️  Actividad #{$actividad->id} no tiene fecha de inicio - SALTANDO");
                    $skipped++;
                    continue;
                }

                $tiempoRestante = $this->calculateTimeRemaining($actividad->star_date);

                if (!$isDryRun) {
                    // Enviar notificación usando tu clase ActividadReminderNotification
                    $actividad->user->notify(new ActividadReminderNotification($actividad));

                    // También enviar a usuarios con roles específicos (opcional)
                    $this->notifyAdditionalUsers($actividad);

                    // Marcar como notificada
                    $actividad->update(['reminder_notified_at' => now()]);

                    $this->info("✅ Notificación enviada a: {$actividad->user->name} ({$actividad->user->email})");
                }

                $this->line("📋 Actividad: {$actividad->macroactividad}");
                $this->line("👤 Usuario: {$actividad->user->name}");
                $this->line("⏰ {$tiempoRestante}");
                $this->line("📅 Programada: {$actividad->star_date}");
                $this->newLine();

                $notificationsSent++;
            } catch (\Exception $e) {
                $this->error("❌ Error al procesar actividad #{$actividad->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->displaySummary($notificationsSent, $errors, $skipped, $isDryRun);

        return Command::SUCCESS;
    }

    /**
     * Enviar notificaciones adicionales a supervisores/admins
     */
    private function notifyAdditionalUsers(Actividad $actividad): void
    {
        // Obtener usuarios que deben recibir notificaciones (por ejemplo, administradores)
        $additionalUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor']); // Ajusta según tus roles
        })->get();

        foreach ($additionalUsers as $user) {
            if ($user->id === $actividad->user->id) {
                continue; // No enviar duplicado al usuario principal
            }

            $user->notify(new ActividadReminderNotification($actividad));
        }
    }

    private function calculateTimeRemaining($fechaInicio)
    {
        $now = now();
        $fechaInicio = Carbon::parse($fechaInicio);

        if ($fechaInicio->isPast()) {
            $diff = $now->diff($fechaInicio);
            return "⚠️  Debería haber comenzado hace {$diff->h}h {$diff->i}m";
        }

        $diff = $now->diff($fechaInicio);
        
        if ($diff->days > 0) {
            return "⏳ Comienza en {$diff->days}d {$diff->h}h {$diff->i}m";
        }
        
        return "⏳ Comienza en {$diff->h}h {$diff->i}m";
    }

    private function showUpcomingActivities(): void
    {
        $upcoming = Actividad::whereNotNull('reminder_at')
            ->where('reminder_at', '>', now())
            ->where('estado', '!=', 'Completada')
            ->with('user')
            ->orderBy('reminder_at')
            ->limit(5)
            ->get();

        if ($upcoming->isEmpty()) return;

        $this->info('📅 PRÓXIMAS ACTIVIDADES:');
        foreach ($upcoming as $actividad) {
            $reminderTime = Carbon::parse($actividad->reminder_at)->format('Y-m-d H:i');
            $userName = $actividad->user ? $actividad->user->name : 'Sin usuario';
            $this->line("• {$actividad->macroactividad} ({$reminderTime}) - Usuario: {$userName}");
        }
    }

    private function displaySummary(int $sent, int $errors, int $skipped, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('📊 RESUMEN');
        
        if ($isDryRun) {
            $this->warn("🔍 MODO DRY RUN - Notificaciones que se habrían enviado: {$sent}");
        } else {
            $this->info("✅ Notificaciones enviadas: {$sent}");
        }
        
        if ($skipped) $this->warn("⏭️ Omitidas: {$skipped}");
        if ($errors) $this->error("❌ Errores: {$errors}");

        if (!$isDryRun && $sent > 0) {
            $this->info("🔔 Las notificaciones aparecerán en la campanita de cada usuario");
        }
    }
}