<?php

use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Punto de entrada: siempre al login del panel de Filament.
| Ajusta el path si tu panel no es /admin.
*/

// Home → login del panel
Route::redirect('/', '/admin/login')->name('home');

// (Opcional) Fallback: cualquier ruta no encontrada → login
Route::fallback(fn () => redirect('/admin/login'));

// Salir de impersonación
Route::get('impersonate/leave', function () {
    if (! app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }

    app(ImpersonateManager::class)->leave();

    return redirect(
        session()->pull('impersonate.back_to', '/')
    );
})->name('impersonate.leave')->middleware('web');
