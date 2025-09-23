<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// AGREGADO: Regla para autorizar el canal de notificaciones en tiempo real
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (string) $user->getAuthIdentifier() === (string) $userId;
});