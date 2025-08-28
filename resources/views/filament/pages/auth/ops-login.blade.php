@php
    $panel = filament()->getPanel();
    $brandName = $panel->getBrandName() ?? 'OPS-OD';
    $logo = $panel->getBrandLogo() ?? asset('images/logo-azul-fondo-transparente (002).png');
@endphp

<x-filament-panels::page.simple>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0A2C65] to-[#0B3578] p-6">
        <div class="w-full max-w-md">
            <div class="bg-white/95 dark:bg-slate-900/70 backdrop-blur rounded-2xl shadow-2xl p-8">
                {{-- Encabezado --}}
                <div class="flex flex-col items-center gap-3 mb-6">
                    <img src="{{ $logo }}" alt="Logo" class="h-14">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">{{ $brandName }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Inicia sesión para continuar</p>
                </div>

                {{-- Formulario (usa el de Filament) --}}
                <x-filament-panels::form :wire:submit="'authenticate'">
                    {{ $this->form }}

                    <x-filament::button type="submit" color="primary" class="w-full mt-4">
                        Entrar
                    </x-filament::button>
                </x-filament-panels::form>

                {{-- Acciones secundarias --}}
                <div class="mt-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    {{-- Ruta de reset: usa la del panel /admin/password-reset --}}
                    <a href="{{ url($panel->getPath().'/password-reset') }}" class="hover:underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                    <button x-data @click="$dispatch('theme-toggled')" class="hover:underline">Tema</button>
                </div>
            </div>

            {{-- Footer --}}
            <p class="mt-6 text-center text-xs text-white/80">
                © {{ now()->year }} {{ $brandName }} — Todos los derechos reservados
            </p>
        </div>
    </div>
</x-filament-panels::page.simple>
