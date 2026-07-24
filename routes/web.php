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
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit')->middleware('throttle:10,1');

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

/* -----------------------------
|  REPORTES PDF GENERALES
------------------------------*/
Route::middleware(['web', 'auth'])->get('/reportes/actividades/pdf', function () {
    $user = auth()->user();
    $query = \App\Models\Actividad::query()->with(['departamental', 'user']);

    if (! $user->isCentralRole()) {
        $query->where('departamental_id', $user->departamental_id);
    }

    $actividades = $query->orderByDesc('created_at')->get();
    $titulo = $user->isCentralRole() ? 'Todas las departamentales' : ($user->departamental?->nombre ?? 'Mi departamental');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.actividades_reporte', compact('actividades', 'titulo'));

    return $pdf->download('reporte_actividades_'.now()->format('Y-m-d').'.pdf');
})->name('reportes.actividades.pdf');

Route::middleware(['web', 'auth'])->get('/reportes/tickets/pdf', function () {
    $user = auth()->user();
    $query = \App\Models\Ticket::query()->with('departamental');

    if (! $user->isCentralRole()) {
        $query->where('departamental_id', $user->departamental_id);
    }

    $tickets = $query->orderByDesc('created_at')->get();
    $titulo = $user->isCentralRole() ? 'Todas las departamentales' : ($user->departamental?->nombre ?? 'Mi departamental');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tickets_reporte', compact('tickets', 'titulo'));

    return $pdf->download('reporte_tickets_'.now()->format('Y-m-d').'.pdf');
})->name('reportes.tickets.pdf');

Route::middleware(['web', 'auth'])->get('/reportes/requisiciones/pdf', function () {
    $user = auth()->user();
    $query = \App\Models\Requisicion::query()->with('departamental');

    if (! $user->isCentralRole()) {
        $query->where('departamental_id', $user->departamental_id);
    }

    $requisiciones = $query->orderByDesc('created_at')->get();
    $titulo = $user->isCentralRole() ? 'Todas las departamentales' : ($user->departamental?->nombre ?? 'Mi departamental');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.requisiciones_reporte', compact('requisiciones', 'titulo'));

    return $pdf->download('reporte_requisiciones_'.now()->format('Y-m-d').'.pdf');
})->name('reportes.requisiciones.pdf');

Route::middleware(['web', 'auth'])->get('/reportes/contratos/pdf', function () {
    $user = auth()->user();
    $query = \App\Models\Contrato::query()->with('departamental');

    if (! $user->isCentralRole()) {
        $query->where('departamental_id', $user->departamental_id);
    }

    $contratos = $query->orderByDesc('created_at')->get();
    $titulo = $user->isCentralRole() ? 'Todas las departamentales' : ($user->departamental?->nombre ?? 'Mi departamental');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.contratos_reporte', compact('contratos', 'titulo'));

    return $pdf->download('reporte_contratos_'.now()->format('Y-m-d').'.pdf');
})->name('reportes.contratos.pdf');

/* -----------------------------
|  PDF DEL MANUAL POR ROL
-------------------------------*/
Route::middleware(['web', 'auth'])->get('/admin/manual', function () {
    $user = auth()->user();
    $role = $user->getRoleNames()->first();
    $map = [
        'super_admin' => 'manual-super-admin.pdf',
        'coordinador' => 'manual-coordinador.pdf',
        'asistente_tecnico' => 'manual-usuario-final.pdf',
    ];
    $file = $map[$role] ?? null;
    if (! $file) {
        abort(403, 'No hay manual disponible para su rol.');
    }
    $path = base_path("docs/manuales/pdf/{$file}");
    if (! file_exists($path)) {
        abort(404, 'Archivo de manual no encontrado.');
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('manual.pdf');

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
    $path = $media->getPathRelativeToRoot();

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
