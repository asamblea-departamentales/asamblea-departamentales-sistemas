<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/banners/{banner}/impression', function (App\Models\Banner\Content $banner) {
    $banner->trackImpression();

    return response()->json(['success' => true]);
});

Route::middleware('auth:sanctum')->post('/banners/{banner}/click', function (App\Models\Banner\Content $banner) {
    $banner->trackClick();

    return response()->json(['success' => true]);
});
