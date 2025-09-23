@php
    use Filament\Support\Enums\MaxWidth;
    $navigation = filament()->getNavigation();
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div class="fi-layout flex min-h-screen w-full flex-row-reverse overflow-x-clip">
        {{-- Contenedor principal --}}
        <div @class(['fi-main-ctn w-screen flex-1 flex-col'])>
            {{-- Topbar --}}
            @if (filament()->hasTopbar())
                <x-filament-panels::topbar :navigation="$navigation" />
            @endif

            {{-- Contenido principal --}}
            <main class="fi-main mx-auto h-full w-full px-4 md:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        {{-- Sidebar --}}
        @if (filament()->hasNavigation())
            <x-filament-panels::sidebar :navigation="$navigation" class="fi-main-sidebar" />
        @endif
    </div>
</x-filament-panels::layout.base>
