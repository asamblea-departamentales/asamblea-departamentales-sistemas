<?php
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Http\Controllers\ContactController;

// Rutas de contacto PRIMERO
Route::get('/contact', fn() => view('contact'))->name('contact.form');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Otras rutas
Route::redirect('/', '/admin/login')->name('home');

Route::get('impersonate/leave', function () {
    if (! app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }
    app(ImpersonateManager::class)->leave();
    return redirect(session()->pull('impersonate.back_to', '/'));
})->name('impersonate.leave');

// Fallback al final
Route::fallback(fn () => redirect('/admin/login'));
