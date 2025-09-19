<?php

namespace App\Console\Commands;

use App\Models\Actividad;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;

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
                    // Enviar notificación directa de Filament
                    FilamentNotification::make()
                        ->title('Recordatorio de Actividad')
                        ->body("La actividad '{$actividad->macroactividad}' comienza pronto.")
                        ->success()
                        ->send();

                    // Marcar como notificada
                    $actividad->update(['reminder_notified_at' => now()]);
                }

                $this->line("📋 Actividad: {$actividad->macroactividad}");
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

    private function calculateTimeRemaining($fechaInicio)
    {
        $now = now();
        $fechaInicio = Carbon::parse($fechaInicio);

        if ($fechaInicio->isPast()) {
            $diff = $now->diff($fechaInicio);
            return "⚠️  Debería haber comenzado hace {$diff->h}h {$diff->i}m";
        }

        $diff = $now->diff($fechaInicio);
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
            $this->line("• {$actividad->macroactividad} ({$reminderTime}) - Usuario: {$actividad->user->name}");
        }
    }

    private function displaySummary(int $sent, int $errors, int $skipped, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('📊 RESUMEN');
        $this->info("✅ Notificaciones enviadas: {$sent}");
        if ($skipped) $this->warn("⏭️ Omitidas: {$skipped}");
        if ($errors) $this->error("❌ Errores: {$errors}");
    }
}
