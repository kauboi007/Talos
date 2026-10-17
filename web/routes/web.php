<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/login', 'pages::login')->name('login');
Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
