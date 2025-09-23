@props(['livewire' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
      class="fi min-h-screen">
<head>
    <script>console.log('🔍 BASE.BLADE.PHP CARGADO');</script>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $livewire?->getRenderHookScopes()) }}

    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="user-id" content="{{ auth()->user()?->getAuthIdentifier() }}">

    @if ($favicon = filament()->getFavicon())
        <link rel="icon" href="{{ $favicon }}" />
    @endif

    <title>{{ filled($livewire?->getTitle()) ? $livewire->getTitle() . ' - ' : '' }}{{ filament()->getBrandName() }}</title>

    @filamentStyles
    {{ filament()->getTheme()->getHtml() }}
    {{ filament()->getFontHtml() }}
    <style>
        :root { --font-family: '{!! filament()->getFontFamily() !!}'; }
        [x-cloak] { display: none !important; }

        /* Forzar modo claro */
        body, .fi-body, .fi-main-ctn {
            background-color: #f9fafb !important;
            color: #111827 !important;
        }
    </style>

    @stack('styles')
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $livewire?->getRenderHookScopes()) }}
</head>
<body {{ $attributes->merge(($livewire ?? null)?->getExtraBodyAttributes() ?? [], escape: false)->class([
    'fi-body',
    'fi-panel-' . filament()->getId(),
    'min-h-screen font-normal antialiased',
]) }}>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $livewire?->getRenderHookScopes()) }}

    {{ $slot }}

    {{-- Componente Livewire de notificaciones --}}
    @livewire(\Filament\Notifications\Livewire\Notifications::class)

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

    @filamentScripts(withCore: true)

    {{-- Configuración Echo / WebSocket --}}
    @if (filament()->hasBroadcasting() && config('filament.broadcasting.echo'))
        <script data-navigate-once>
            window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')));

            document.addEventListener('livewire:load', () => {
                const userId = document.querySelector('meta[name="user-id"]')?.content;
                console.log('Echo cargado, usuario:', userId);

                if (!userId) return;

                window.Echo.private(`notifications.${userId}`)
                    .notification((notification) => {
                        console.log('📬 Notificación recibida y procesada:', notification);

                        // Toast Filament
                        window.showToastNotification?.({
                            title: notification.title || 'Nueva Notificación',
                            body: notification.body || '',
                            icon: notification.icon || 'heroicon-o-bell',
                            iconColor: notification.iconColor || 'primary',
                            duration: notification.duration === 'persistent' ? null : 5000,
                            actions: notification.actions || [],
                        });

                        // Badge y refresco Livewire
                        window.updateFilamentBadge?.();
                        window.refreshLivewireNotifications?.();
                    });
            });
        </script>
    @endif

    @stack('scripts')
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $livewire?->getRenderHookScopes()) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $livewire?->getRenderHookScopes()) }}

    @vite('resources/js/app.js')
</body>
</html>
