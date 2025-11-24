<?php

use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Http\Controllers\ContactController;
use App\Models\Departamental;
use App\Models\User;
use App\Notifications\ActividadReminderNotification;

/* -----------------------------
|  CONTACTO
------------------------------*/
Route::get('/contact', fn() => view('contact'))->name('contact.form');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

/* -----------------------------
|  HOME → REDIRECT A PANEL
------------------------------*/
Route::redirect('/', '/admin/login')->name('home');

/* -----------------------------
|  RUTA DE LOGIN NECESARIA
|  (Filament USE login ROUTE)
------------------------------*/
Route::middleware('web')->get('/login', function () {
    return redirect('/admin/login');
})->name('login');

/* -----------------------------
|  TEST DE NOTIFICACIONES
------------------------------*/
Route::post('/test-notification', function () {
    try {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        
        $actividad = \App\Models\Actividad::first();
        
        if (!$actividad) {
            return response()->json(['error' => 'No hay actividades'], 404);
        }
        
        $user->notify(new ActividadReminderNotification($actividad));
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada correctamente',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'actividad' => $actividad->macroactividad
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('web');

/* -----------------------------
|  API → CONTEO DE NOTIFICACIONES
------------------------------*/
Route::middleware(['web', 'auth'])->get('/api/notifications/unread-count', function () {
    return response()->json([
        'count' => auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0
    ]);
});

/* -----------------------------
|  IMPERSONATE DE DEPARTAMENTAL
------------------------------*/
Route::middleware(['web', 'auth'])
    ->get('/admin/impersonate-departamental/{departamental}', function (Departamental $departamental) {

        $super = auth()->user();

        if (!$super || !$super->canImpersonate()) {
            abort(403, 'No autorizado.');
        }

        $user = User::where('departamental_id', $departamental->id)->firstOrFail();

        session()->put('impersonate.back_to', url()->previous());

        $super->impersonate($user);

        return redirect('/admin');
    })
    ->name('admin.impersonate.departamental');

/* -----------------------------
|  SALIR DEL IMPERSONATE
------------------------------*/
Route::middleware(['web', 'auth'])->get('/admin/impersonate/leave', function () {
    $impersonate = app(\Lab404\Impersonate\Services\ImpersonateManager::class);

    if (! $impersonate->isImpersonating()) {
        return redirect('/admin');
    }

    $impersonate->leave(); // Sal del impersonate
    request()->session()->regenerate(); // Regenera la sesión por seguridad

    return redirect(session()->pull('impersonate.back_to', '/admin'))
        ->with('success', 'Has salido del modo de suplantación');
})->name('impersonate.leave');


/* -----------------------------
|  FALLBACK
------------------------------*/
Route::fallback(fn () => redirect('/admin/login'));
