<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Hanya untuk yang BELUM login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    Route::get('/register', function () {
        return view('register');
    });

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Hanya untuk yang SUDAH login (semua halaman lain)
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/events');
    });

    Route::get('/events', function () {
        return view('events');
    });

    Route::get('/profil', function () {
        return view('profil');
    });

    Route::get('/event-saya', function () {
        return view('event-saya');
    });

    Route::get('/race-kit', function () {
        return view('race-kit');
    });

    Route::get('/change-category', function () {
        return view('change-category');
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

// Link yang tidak dikenal: kalau belum login, arahkan ke login
Route::fallback(function () {
    return auth()->check() ? abort(404) : redirect()->route('login');
});