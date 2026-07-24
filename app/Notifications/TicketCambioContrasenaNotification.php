<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketCambioContrasenaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public User $solicitante,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Solicitud de cambio de contraseña',
            'body' => 'El usuario '.$this->solicitante->firstname.' '.$this->solicitante->lastname
                      .' ('.$this->solicitante->email.') ha solicitado un cambio de contraseña.',
            'ticket_id' => $this->ticket->id,
            'url' => '/admin/tickets/'.$this->ticket->id,
        ];
    }
}
