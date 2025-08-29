<?php
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Http\Controllers\ContactController;

// ---------------------------
// Rutas públicas de contacto (PRIMERO)
// ---------------------------
Route::get('/contact', function() {
    return view('contact');
})->name('contact.form');

Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Home → login del panel
Route::redirect('/', '/admin/login')->name('home');

// Impersonate leave
Route::get('impersonate/leave', function () {
    if (! app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }
    app(ImpersonateManager::class)->leave();
    return redirect(session()->pull('impersonate.back_to', '/'));
})->name('impersonate.leave');

// Fallback (ÚLTIMO)
Route::fallback(function () {
    return redirect('/admin/login');
});
