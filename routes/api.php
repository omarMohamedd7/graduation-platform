<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProjectUpdateController;
use App\Http\Controllers\SupervisorNoteController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
    
    // User management
    Route::apiResource('users', UserController::class);

    // User role-specific endpoints
    Route::get('/students', [UserController::class, 'getStudents']);
    Route::get('/supervisors', [UserController::class, 'getSupervisors']);
    Route::get('/committee-heads', [UserController::class, 'getCommitteeHeads']);
    Route::get('/departments', [UserController::class, 'getDepartments']);

    // Proposal routes - accessible to all authenticated users
    Route::post('/proposals', [ProposalController::class, 'store']);
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    Route::post('/proposals/{id}/approve', [ProposalController::class, 'approve']);
    Route::post('/proposals/{id}/reject', [ProposalController::class, 'reject']);
    Route::post('/proposals/{id}/assign-supervisor', [ProposalController::class, 'assignSupervisor']);
    Route::post('/proposals/{id}/respond-assignment', [ProposalController::class, 'respondAssignment']);
    
    // Project Update routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Project updates routes
        Route::post('/project-updates', [ProjectUpdateController::class, 'store']);
        Route::get('/projects/{id}/updates', [ProjectUpdateController::class, 'index']);
    });
    // Supervisor Note routes
    Route::post('/projects/{id}/supervisor-notes', [SupervisorNoteController::class, 'store']);
    Route::get('/projects/{id}/supervisor-notes', [SupervisorNoteController::class, 'index']);
});

// Add this route for direct testing
Route::get('/test-register', function() {
    $user = \App\Models\User::create([
        'full_name' => 'Test User',
        'email' => 'test'.time().'@example.com', // Use timestamp for unique email
        'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
        'role' => \App\Models\User::ROLE_SUPERVISOR,
        'department' => 'Computer Science'
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Test user created successfully',
        'data' => $user,
        'debug' => [
            'valid_roles' => [
                'STUDENT' => \App\Models\User::ROLE_STUDENT,
                'SUPERVISOR' => \App\Models\User::ROLE_SUPERVISOR,
                'COMMITTEE_HEAD' => \App\Models\User::ROLE_COMMITTEE_HEAD
            ]
        ]
    ]);
});



