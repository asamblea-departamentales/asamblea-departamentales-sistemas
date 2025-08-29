<?php
use Illuminate\Support\Facades\Route;

// Clear all caches in testing
if (app()->environment('testing')) {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
}

// Test routes - muy simple
Route::get('/contact', function() {
    return response('Contact GET works', 200);
});

Route::post('/contact', function() {
    return response('Contact POST works', 200);
});

// Fallback
Route::fallback(function() {
    return redirect('/admin/login');
});
