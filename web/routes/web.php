<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');
Route::inertia('/login', 'Login')->name('login');
Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
Route::inertia('/personnel', 'Personnel')->name('personnel');
Route::inertia('/routing', 'Routing')->name('routing');
Route::inertia('/logs', 'Logs')->name('logs');
Route::inertia('/settings', 'Settings')->name('settings');