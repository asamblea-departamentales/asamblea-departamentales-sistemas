<?php
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Http\Controllers\ContactController;
use App\Notifications\ActividadReminderNotification;

// Rutas de contacto PRIMERO
Route::get('/contact', fn() => view('contact'))->name('contact.form');
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

// RUTA DE PRUEBA - DEBE IR ANTES DEL FALLBACK
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

// RUTA PARA CONTEO DE NOTIFICACIONES
Route::middleware(['web', 'auth'])->get('/api/notifications/unread-count', function () {
    if (!auth()->check()) {
        return response()->json(['count' => 0]);
    }
    
    return response()->json([
        'count' => auth()->user()->unreadNotifications()->count()
    ]);
});

// Fallback SIEMPRE AL FINAL
Route::fallback(fn () => redirect('/admin/login'));