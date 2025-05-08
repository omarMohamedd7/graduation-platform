<?php

use App\Http\Controllers\dashboard_controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProposalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// Auth views routes
Route::get('/login', function () {
    return view('sessions.create');
})->name('login');

Route::get('/register', function () {
    return view('register.create');
})->name('register');

// Auth commands routes
Route::post('/login', [UserController::class, 'login'])->name('api.login');
Route::post('/register', [UserController::class, 'register'])->name('api.register');


// Profile route
Route::get('/profile', function () {
    return view('profile');
})->middleware('auth')->name('profile');

Route::middleware('auth')->group(function(){
    Route::get('/dashboard', [dashboard_controller::class, 'index'])
     ->middleware('auth')
     ->name('dashboard');

    Route::post('/proposal/submit', [ProposalController::class, 'store'])->name('add-proposal');

    Route::get('/logout', [UserController::class, 'logout'])->name('logout');
});


