<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="user-id" content="{{ Auth::id() }}">
    <title>{{ config('app.name') }}</title>

    @filamentStyles
    @vite('resources/css/app.css')
</head>
<body class="antialiased">
    {{ $slot }}

    @livewire(\Filament\Notifications\Livewire\Notifications::class)

    @filamentScripts

    @if (config('filament.broadcasting.echo'))
        <script>
        window.Echo = new window.EchoFactory(@js(config('filament.broadcasting.echo')));

        document.addEventListener('DOMContentLoaded', () => {
            const userId = document.querySelector('meta[name="user-id"]')?.content;
            console.log('Echo cargado en app.blade, usuario:', userId);

            if (userId) {
                window.Echo.private(`notifications.${userId}`)
                    .notification((notification) => console.log('Notificación recibida en app.blade:', notification))
                    .subscribed(() => console.log('Suscrito al canal desde app.blade'))
                    .error((error) => console.error('Error en canal:', error));
            }
        });
        </script>
    @endif

    @vite('resources/js/app.js')
</body>
</html>
