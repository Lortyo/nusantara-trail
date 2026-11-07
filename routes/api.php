<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\Api\CategoryController;

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

    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{id}', [EventController::class, 'show']);
    Route::get('events/{eventId}/categories', [CategoryController::class, 'index']);

    // ============ PROTECTED ROUTES (Require JWT) ============
    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);

        // ============ ADMIN ONLY ROUTES (Require JWT + Admin Role) ============
        Route::middleware('admin')->group(function () {
            Route::post('events', [AdminEventController::class, 'store']);
            Route::put('events/{id}', [AdminEventController::class, 'update']);
            Route::delete('events/{id}', [AdminEventController::class, 'destroy']);
            Route::post('events/{eventId}/categories', [CategoryController::class, 'store']);
            Route::put('categories/{id}', [CategoryController::class, 'update']);
            Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
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