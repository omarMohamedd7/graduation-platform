<?php

use App\Http\Controllers\dashboard_controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\WebProjectEvaluationController;
use App\Models\User;
use App\Http\Controllers\ProjectController;

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
    
    // Project routes
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    
    // Document routes
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/projects/{project}/documents', [DocumentController::class, 'index'])->name('documents.project.index');
    Route::get('/project-updates/{projectUpdate}/documents', [DocumentController::class, 'indexForProjectUpdate'])->name('documents.project-update.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Project evaluation routes - accessible to all users
    Route::get('/projects/{project}/evaluation', [WebProjectEvaluationController::class, 'show'])->name('projects.evaluation');
    Route::get('/evaluations/{evaluation}', [WebProjectEvaluationController::class, 'showById'])->name('evaluations.show');
    
    // Committee head evaluation routes
    Route::middleware(['role:'.User::ROLE_COMMITTEE_HEAD])->group(function() {
        Route::get('/evaluations/initialize', [WebProjectEvaluationController::class, 'initializeForm'])
            ->name('evaluations.initialize.form');
        Route::post('/evaluations/initialize', [WebProjectEvaluationController::class, 'initialize'])
            ->name('evaluations.initialize');
        Route::post('/evaluations/submit', [WebProjectEvaluationController::class, 'submitEvaluation'])
            ->name('evaluations.submit');
    });
});


