<div class="space-y-4">
    @if($hasPendingRequest && $pendingTicket)
        <!-- Alerta de solicitud pendiente -->
        <div class="rounded-lg bg-warning-50 dark:bg-warning-900/10 p-4 border border-warning-200 dark:border-warning-700">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-warning-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-warning-800 dark:text-warning-200">
                        Solicitud Pendiente
                    </h3>
                    <p class="text-sm text-warning-700 dark:text-warning-300 mt-1">
                        Ya tiene una solicitud de cambio de contraseña en proceso.
                    </p>
                    <div class="mt-3 space-y-1 text-xs text-warning-600 dark:text-warning-400">
                        <p><span class="font-medium">Ticket:</span> #{{ $pendingTicket->id }}</p>
                        <p><span class="font-medium">Solicitado:</span> {{ $pendingTicket->fecha_solicitud->format('d/m/Y H:i') }}</p>
                        <p><span class="font-medium">Estado:</span> {{ $pendingTicket->estado_interno }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Botón para solicitar cambio -->
        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Cambio de Contraseña
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Para cambiar su contraseña, debe crear una solicitud que será procesada por el departamento de TI.
                    </p>
                    <div class="mt-3">
                        <x-filament::button 
                            wire:click="request" 
                            color="primary"
                            icon="heroicon-o-key"
                        >
                            Solicitar Cambio de Contraseña
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>