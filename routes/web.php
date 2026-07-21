<?php

use App\Http\Controllers\ContactController;
use App\Models\CierreMensual;
use App\Models\Departamental;
use App\Models\User;
use App\Notifications\ActividadReminderNotification;
// Agregado para cierren mensual
use Illuminate\Support\Facades\Route;

/* -----------------------------
|  CONTACTO
------------------------------*/
Route::get('/contact', fn () => view('contact'))->name('contact.form');
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

        if (! $user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (! $user->hasRole('super_admin')) {
            abort(403, 'No autorizado.');
        }

        $actividad = \App\Models\Actividad::first();

        if (! $actividad) {
            return response()->json(['error' => 'No hay actividades'], 404);
        }

        $user->notify(new ActividadReminderNotification($actividad));

        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada correctamente',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'actividad' => $actividad->macroactividad,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
})->middleware(['web', 'auth', 'throttle:5,1']);

/* -----------------------------
|  API → CONTEO DE NOTIFICACIONES
------------------------------*/
Route::middleware(['web', 'auth'])->get('/api/notifications/unread-count', function () {
    return response()->json([
        'count' => auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0,
    ]);
});

/* -----------------------------
|  IMPERSONATE DE DEPARTAMENTAL
------------------------------*/
Route::middleware(['web', 'auth'])
    ->get('/admin/impersonate-departamental/{departamental}', function (Departamental $departamental) {

        $super = auth()->user();

        if (! $super || ! $super->canImpersonate()) {
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
|  NUEVO: RUTA PARA PDF DE CIERRE MENSUAL
------------------------------*/
Route::middleware(['web', 'auth'])->get('/cierres/{cierre}/pdf', function (CierreMensual $cierre) {
    $user = auth()->user();

    if (! $user->hasAnyRole(['ti', 'gol']) && ! $user->isSuperAdmin()) {
        if ($cierre->departamental_id !== $user->departamental_id) {
            abort(403, 'No autorizado para ver este PDF.');
        }
    }

    if (! $cierre->pdf_path) {
        abort(404, 'PDF no generado.');
    }

    return response()->file(storage_path('app/public/'.$cierre->pdf_path));
})->name('cierre.pdf');

/* -----------------------------
|  RUTA PARA PDF CONSOLIDADO
-----------------------------*/
Route::middleware(['web', 'auth'])->get('/consolidado/{año}/{mes}/pdf', function (int $año, int $mes) {
    $user = auth()->user();

    if (! $user->hasAnyRole(['ti', 'gol']) && ! $user->isSuperAdmin()) {
        abort(403, 'No autorizado para ver consolidados.');
    }

    $filename = "informe_consolidado_{$año}_{$mes}.pdf";
    $path = storage_path("app/public/cierres/{$filename}");

    if (! file_exists($path)) {
        abort(404, 'PDF consolidado no generado.');
    }

    return response()->file($path);
})->name('consolidado.pdf');
// * -----------------------------
//  RUTA PARA SERVIR ARCHIVOS MEDIA
// ------------------------------*/
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Route::middleware(['web', 'auth'])->get('/media/{media}', function (Media $media) {

    $user = auth()->user();

    if (! $user || ! $user->can('view_actividad')) {
        abort(403);
    }

    $model = $media->model;

    if ($model instanceof \App\Models\Actividad) {
        if (! $model->canViewMedia()) {
            abort(403, 'No autorizado para ver este archivo.');
        }
    } else {
        abort(403, 'Tipo de archivo no soportado para visualización.');
    }

    $disk = $media->disk;
    $path = $media->getPath();

    if (! Storage::disk($disk)->exists($path)) {
        abort(404, 'Archivo no encontrado');
    }

    // Descargar
    if (request()->query('download')) {
        return Storage::disk($disk)->download(
            $path,
            $media->file_name
        );
    }

    // Preview
    return response()->file(
        Storage::disk($disk)->path($path)
    );

})->name('media.view');

// * -----------------------------
//  FALLBACK (EXCLUYENDO STORAGE Y PDF)
// ------------------------------*/
Route::any('{any}', fn () => redirect('/admin/login'))
    ->where('any', '^(?!storage|cierres|media).*');
