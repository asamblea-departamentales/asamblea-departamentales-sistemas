<?php

namespace App\Exceptions;

use BezhanSalleh\FilamentExceptions\FilamentExceptions;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                FilamentExceptions::report($e);
            }
        });
    }

    // Para las tostadas
    public function render($request, Throwable $exception)
    {
        // Solo manejar excepciones de autorización en el panel de filament
        if (($request->is('admin/*') || $request->routeIs('filament.*'))
            && $exception instanceof AuthorizationException) {

            Notification::make()
                ->title('Acceso denegado')
                ->body('No tienes permiso para realizar esta acción.')
                ->danger()
                ->send();

            return redirect()->back();
        }

        // Llamar al método padre para otras excepciones
        return parent::render($request, $exception);
    }
}
