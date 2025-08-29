<?php
use Illuminate\Support\Facades\Route;

// Test routes - muy simple
Route::get('/contact', function() {
    return 'Contact GET works';
});

Route::post('/contact', function() {
    return 'Contact POST works';
});

// Fallback
Route::fallback(function() {
    return redirect('/admin/login');
});
