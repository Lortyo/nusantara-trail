<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\CategoryChangeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;

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

    Route::get('/profil', [ProfileController::class, 'show'])->name('profil.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::post('/profil/edit', [ProfileController::class, 'update'])->name('profil.update');

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events/{id}/subscribe', [EventController::class, 'subscribe']);
    Route::post('/events/{id}/register', [RegistrationController::class, 'store']);

    Route::get('/event-saya', [RegistrationController::class, 'index']);
    Route::get('/registrations/{id}/pay', [RegistrationController::class, 'pay']);
    Route::post('/registrations/{id}/checkout', [PaymentController::class, 'checkoutRegistration']);
    Route::delete('/registrations/{id}', [RegistrationController::class, 'cancel']);

    Route::get('/change-category', [CategoryChangeController::class, 'show']);
    Route::post('/change-category', [CategoryChangeController::class, 'store']);
    Route::get('/category-changes/{id}/pay', [CategoryChangeController::class, 'pay']);
    Route::post('/category-changes/{id}/checkout', [PaymentController::class, 'checkoutChange']);

    Route::post('/payments/{orderId}/sync', [PaymentController::class, 'sync']);

    Route::get('/race-kit', function () {
        return view('race-kit');
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [AdminEventController::class, 'update'])->name('events.update');
    Route::patch('/events/{id}/status', [AdminEventController::class, 'changeStatus'])->name('events.status');
    Route::delete('/events/{id}', [AdminEventController::class, 'destroy'])->name('events.destroy');

    Route::post('/events/{eventId}/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
});

Route::post('/midtrans/notification', [PaymentController::class, 'notification']);

// Link yang tidak dikenal: kalau belum login, arahkan ke login
Route::fallback(function () {
    return auth()->check() ? abort(404) : redirect()->route('login');
});