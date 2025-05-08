<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProjectUpdateController;
use App\Http\Controllers\SupervisorNoteController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectEvaluationController;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

// Public routes - accessible without authentication
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Common auth routes - accessible to all authenticated users
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
    
    // User management - accessible to all authenticated users
    Route::apiResource('users', UserController::class);
    
    // User role-specific endpoints - accessible to all authenticated users
    Route::get('/students', [UserController::class, 'getStudents']);
    Route::get('/supervisors', [UserController::class, 'getSupervisors']);
    Route::get('/committee-heads', [UserController::class, 'getCommitteeHeads']);
    Route::get('/departments', [UserController::class, 'getDepartments']);

    // ===== PROPOSALS =====
    
    // Proposal retrieval - accessible to all authenticated users
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    
    // Student-only proposal routes
    Route::middleware('role:'.User::ROLE_STUDENT)->group(function () {
        Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    });
    // Committee head-only proposal routes
    Route::middleware(['role:'.User::ROLE_COMMITTEE_HEAD])->group(function () {
        Route::post('/proposals/{id}/approve', [ProposalController::class, 'approve']);
        Route::post('/proposals/{id}/reject', [ProposalController::class, 'reject']);
        Route::post('/proposals/{id}/assign-supervisor', [ProposalController::class, 'assignSupervisor']);
    });
    
    // Supervisor-only proposal routes
    Route::middleware(['role:'.User::ROLE_SUPERVISOR])->group(function () {
        Route::post('/proposals/{id}/respond-assignment', [ProposalController::class, 'respondAssignment']);
    });
    
    // ===== PROJECT UPDATES =====
    
    // Student-only project update routes
    Route::middleware(['role:'.User::ROLE_STUDENT])->group(function () {
        Route::post('/project-updates', [ProjectUpdateController::class, 'store']);
    });
    
    // Project updates retrieval - accessible to students and supervisors
    // (Controller will handle additional authorization based on project ownership)
    Route::middleware(['role:'.User::ROLE_STUDENT.','.User::ROLE_SUPERVISOR])->group(function () {
        Route::get('/projects/{id}/updates', [ProjectUpdateController::class, 'index']);
    });
    
    // ===== SUPERVISOR NOTES =====
    
    // Supervisor-only notes routes
    Route::middleware(['role:'.User::ROLE_SUPERVISOR])->group(function () {
        Route::post('/projects/{id}/supervisor-notes', [SupervisorNoteController::class, 'store']);
        Route::get('/projects/{id}/supervisor-notes', [SupervisorNoteController::class, 'index']);
    });
    
    // ===== DOCUMENTS =====
    
    // Document routes - accessible to students and supervisors
    // (Controller will handle additional authorization based on project ownership)
    Route::middleware(['role:'.User::ROLE_STUDENT.','.User::ROLE_SUPERVISOR])->group(function () {
        // Project document routes
        Route::post('/projects/{project}/documents', [DocumentController::class, 'uploadForProject']);
        Route::get('/projects/{project}/documents', [DocumentController::class, 'getForProject']);
        
        // Project update document routes
        Route::post('/project-updates/{projectUpdate}/documents', [DocumentController::class, 'uploadForProjectUpdate']);
        
        // Document general routes
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    });
    
    // ===== PROJECT EVALUATIONS =====
    
    // Evaluation retrieval - accessible to students, supervisors, and committee heads
    Route::get('/projects/{projectId}/evaluation', [ProjectEvaluationController::class, 'getForProject']);
    Route::get('/evaluations/{id}', [ProjectEvaluationController::class, 'show']);
    
    // Committee head-only evaluation routes
    Route::middleware(['role:'.User::ROLE_COMMITTEE_HEAD])->group(function () {
        Route::post('/evaluations/initialize', [ProjectEvaluationController::class, 'initialize']);
        Route::post('/evaluations/committee-evaluation', [ProjectEvaluationController::class, 'submitCommitteeEvaluation']);
    });
    
    // Supervisor-only evaluation routes
    Route::middleware(['role:'.User::ROLE_SUPERVISOR])->group(function () {
        Route::post('/evaluations/supervisor-evaluation', [ProjectEvaluationController::class, 'submitSupervisorEvaluation']);
    });
});

// Development-only routes
// These should be removed or restricted before moving to production
if (app()->environment('local', 'development')) {
    Route::get('/test-register', function() {
        $user = \App\Models\User::create([
            'full_name' => 'Test User',
            'email' => 'test'.time().'@example.com', // Use timestamp for unique email
            'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
            'role' => \App\Models\User::ROLE_SUPERVISOR,
            'department' => 'Computer Science'
        ]);
        
        Log::info('Test user created', ['user_id' => $user->id, 'email' => $user->email]);
        
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
}

