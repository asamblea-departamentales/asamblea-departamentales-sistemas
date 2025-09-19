<?php

namespace App\Notifications;

use App\Models\Actividad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ActividadReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $actividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
    }

    //Canales: database + broadcast para campanita en tiempo real
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    //Como se guarda en la db
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Recordatorio de Actividad',
            'body' => "La actividad '{$this->actividad->macroactividad}' comienza pronto.",
            'url' => \App\Filament\Resources\ActividadResource::getUrl('view', [
                'record' => $this->actividad,
            ]),
        ];
    }

     // Cómo se envía vía broadcast (opcional, para notificación en tiempo real)
     public function toBroadcast($notifiable)
     {
         return new BroadcastMessage([
             'title' => 'Recordatorio de actividad',
             'body' => "La actividad '{$this->actividad->macroactividad}' comienza pronto.",
            'url'  => \App\Filament\Resources\ActividadResource::getUrl('view', ['record' => $this->actividad]),
         ]);
     }

     // Alternativamente, Laravel también requiere toArray() para broadcast
     public function toArray($notifiable)
    {
       return [
        'title' => 'Recordatorio de Actividad',
        'body'  => "La actividad '{$this->actividad->macroactividad}' comienza pronto.",
        'url'   => \App\Filament\Resources\ActividadResource::getUrl('view', ['record' => $this->actividad]),
    ];
    }
}
