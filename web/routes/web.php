<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/login', 'pages::login')->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/profile', 'pages::profile')->name('profile');

    Route::middleware('role:base_admin')->group(function () {
        Route::livewire('/personnel', 'pages::personnel')->name('personnel');
        Route::livewire('/checkpoint-graph', 'pages::checkpoint-graph')->name('checkpoint-graph');
    });

    Route::middleware('role:hq_admin')->group(function () {
        Route::livewire('/missions/create', 'pages::mission-create')->name('missions.create');
    });
    Route::livewire('/missions/{mission}', 'pages::mission-show')->name('missions.show');

    Route::delete('/logout', function () {
        Auth::logout();

        return redirect()->route('home');
    })->name('logout');
});
