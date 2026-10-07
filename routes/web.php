<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController; 
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

    Route::get('/profil', [ProfileController::class, 'show'])->name('profil.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::post('/profil/edit', [ProfileController::class, 'update'])->name('profil.update');

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

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

// Link yang tidak dikenal: kalau belum login, arahkan ke login
Route::fallback(function () {
    return auth()->check() ? abort(404) : redirect()->route('login');
});