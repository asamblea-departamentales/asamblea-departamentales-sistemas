<?php

use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Http\Controllers\ContactController;

// Home → login del panel
Route::redirect('/', '/admin/login')->name('home');

// Fallback
Route::fallback(fn () => redirect('/admin/login'));

// Impersonate leave
Route::get('impersonate/leave', function () {
    if (! app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }
    app(ImpersonateManager::class)->leave();
    return redirect(session()->pull('impersonate.back_to', '/'));
})->name('impersonate.leave')->middleware('web');

// ---------------------------
// Rutas públicas de contacto
// ---------------------------
Route::middleware('web')->group(function () {
    Route::get('/contact', fn() => view('contact'))->name('contact.form');
    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
});
