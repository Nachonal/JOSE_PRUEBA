<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/ping', fn () => response()->json(['message' => 'API working!']));

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::apiResource('products', ProductController::class);
});

// Respuesta JSON para rutas no encontradas bajo /api
Route::fallback(fn () => response()->json(['message' => 'Not Found.'], 404));
