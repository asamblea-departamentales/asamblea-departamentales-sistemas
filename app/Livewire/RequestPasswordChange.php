<?php
/*

namespace App\Livewire;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Component;

class RequestPasswordChange extends Component
{
    // Indica si el usuario ya tiene una solicitud de cambio de contraseña pendiente.
    public $hasPendingRequest = false;
    
    // Almacena el objeto del ticket pendiente encontrado (si existe).
    public $pendingTicket = null;

    // Se ejecuta al iniciar el componente.
    public function mount()
    {
        // Al montar el componente, verifica inmediatamente si hay un ticket pendiente.
        $this->checkPendingRequest();
    }

    // Verifica en la base de datos si existe una solicitud de cambio de contraseña
    // marcada como 'PENDIENTE' para el usuario autenticado.
    public function checkPendingRequest()
    {
        $this->pendingTicket = Ticket::where('tipo_ticket', 'SOLICITUD')
            // Filtra por el motivo específico que contiene el nombre del usuario
            ->where('motivo', 'like', '%Solicitud de cambio de contraseña por parte del usuario ' . auth()->user()->name . '%')
            ->where('estado_interno', 'PENDIENTE')
            ->latest() // Toma el más reciente, aunque solo debería haber uno pendiente.
            ->first();

        $this->hasPendingRequest = $this->pendingTicket !== null;
    }

    // Crea un nuevo ticket de solicitud de cambio de contraseña.
    public function request()
    {
        // 1. Verifica nuevamente antes de crear para prevenir duplicados (race condition).
        $this->checkPendingRequest();

        if ($this->hasPendingRequest) {
            // Si ya hay una pendiente, muestra una notificación de advertencia y detiene la ejecución.
            Notification::make()
                ->title('Solicitud Pendiente')
                ->warning()
                ->body('Ya tiene una solicitud de cambio de contraseña pendiente.')
                ->send();
            
            return;
        }

        try {
            // 2. Crea el registro del ticket en la base de datos.
            Ticket::create([
                'tipo_ticket' => 'SOLICITUD',
                'motivo' => 'Solicitud de cambio de contraseña por parte del usuario ' . auth()->user()->name,
                'fecha_solicitud' => Carbon::now(),
                'estado_interno' => 'PENDIENTE',
                // Asume que el usuario tiene una columna 'departamental_id'.
                'departamental_id' => auth()->user()->departamental_id, 
                'observaciones' => 'El usuario ' . auth()->user()->email . ' ha solicitado un cambio de contraseña.'
            ]);

            // 3. Actualiza el estado local del componente para reflejar la solicitud recién creada.
            $this->checkPendingRequest();

            // 4. Muestra una notificación de éxito al usuario.
            Notification::make()
                ->title('Solicitud Enviada')
                ->success()
                ->body('Se ha creado un ticket para el cambio de contraseña.')
                ->send();
                
        } catch (\Exception $e) {
            // 5. Manejo de errores y notificación si la creación del ticket falla.
            Notification::make()
                ->title('Error')
                ->danger()
                ->body('No se pudo crear la solicitud. Por favor intente nuevamente.')
                ->send();
        }
    }

    // Renderiza la vista del componente Livewire.
    public function render()
    {
        // Se espera que 'livewire.request-password-change' contenga la lógica de vista
        // para mostrar el botón de solicitud o el estado pendiente (basado en $hasPendingRequest).
        return view('livewire.request-password-change');
    }
}

*/
?>