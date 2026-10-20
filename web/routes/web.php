<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/login', 'pages::login')->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/profile', 'pages::profile')->name('profile');
    Route::delete('/logout', function () {
        Auth::logout();

        return redirect()->route('home');
    })->name('logout');
});
