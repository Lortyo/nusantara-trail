<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;

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

// API Version 1
Route::prefix('v1')->group(function () {

    // ============ PUBLIC ROUTES (No Auth) ============

    // Authentication endpoints
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

        // Events (example - akan dibuat nanti)
        // Route::get('events', [EventController::class, 'index']);
        // Route::get('events/{id}', [EventController::class, 'show']);

    });

    // ============ ADMIN ROUTES (Require JWT + Admin Role) ============

    Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
        // Admin routes akan ditambahkan nanti
    });
});

// ============ WEBHOOKS (No Auth - Validate Signature Instead) ============

// Midtrans webhook (existing)
Route::post('/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'notification']);

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'service' => 'Nusantara Trail API',
    ]);
});
