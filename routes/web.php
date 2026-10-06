<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
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

Route::get('/events', function () {
    return view('events');
});

