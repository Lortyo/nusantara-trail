<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version 1
Route::prefix('v1')->group(function () {

    // ============ PUBLIC ROUTES (No Auth) ============
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // Protected auth routes (need JWT token)
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // ============ PROTECTED ROUTES (Require JWT) ============
    Route::middleware('auth:api')->group(function () {

        // Events Read Routes (User & Admin)
        Route::get('events', [AdminEventController::class, 'index']);
        Route::get('events/{id}', [AdminEventController::class, 'show']);

        // Dashboard Summary
        Route::get('dashboard/summary', [AdminDashboardController::class, 'summary']);

        // ============ ADMIN ONLY ROUTES (Require JWT + Admin Role) ============
        Route::middleware('admin')->group(function () {
            Route::post('events', [AdminEventController::class, 'store']);
            Route::put('events/{id}', [AdminEventController::class, 'update']);
            Route::delete('events/{id}', [AdminEventController::class, 'destroy']);
        });
    });
});

// ============ WEBHOOKS & HEALTH CHECK ============
Route::post('/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'notification']);

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'service' => 'Nusantara Trail API',
    ]);
});