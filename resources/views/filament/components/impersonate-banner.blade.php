@php
    $impersonator = session(config('laravel-impersonate.session_key'))
        ? \App\Models\User::find(session(config('laravel-impersonate.session_key')))
        : null;

    $impersonated = Filament\Facades\Filament::auth()->user();

    // Nombre mostrado del usuario suplantado
    $display = ($impersonated && $impersonated->firstname && $impersonated->lastname)
        ? $impersonated->firstname . ' ' . $impersonated->lastname
        : 'Usuario Desconocido';

    // Nombre del usuario que está suplantando
    $impersonatorDisplay = ($impersonator && $impersonator->firstname && $impersonator->lastname)
        ? $impersonator->firstname . ' ' . $impersonator->lastname
        : 'Usuario Desconocido';

    // Nombre de la departamental del usuario suplantado
    $departamentalNombre = ($impersonated && $impersonated->departamental)
        ? $impersonated->departamental->nombre
        : 'Sin departamental';
@endphp

@if(auth()->check() && app('impersonate')->isImpersonating())
    <div class="fixed bottom-0 z-40 flex flex-col items-center justify-center w-full min-h-0" role="status" aria-live="polite">
        <div class="flex items-center justify-between w-full pl-2 mx-auto space-x-2 text-sm text-white bg-warning-600">
            <div class="flex items-center space-x-2">

                <!-- Icono -->
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>

                <span>
                    Viendo como 
                    <span class="font-semibold">{{ $display }}</span>

                    <!-- Departamental -->
                    <span class="ml-1 px-2 py-0.5 rounded bg-primary-500/20">
                        {{ $departamentalNombre }}
                    </span>

                    <!-- Impersonator -->
                    @if($impersonator)
                        <span class="ml-1 px-2 py-0.5 rounded bg-secondary-50/20">
                            por {{ $impersonatorDisplay }}
                        </span>
                    @endif
                </span>
            </div>

            <a href="{{ route('impersonate.leave') }}"
                class="inline-flex items-center px-2 py-1 text-xs font-semibold text-black transition shadow-sm bg-warning-300 hover:bg-warning-100"
                aria-label="Salir de la vista y volver a tu cuenta">

                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>

                <span class="font-bold">Volver</span>
            </a>
        </div>
    </div>
@endif
