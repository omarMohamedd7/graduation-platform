<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectUpdateRequest;
use App\Http\Resources\ProjectUpdateResource;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectUpdateController extends Controller
{
    /**
     * Display a listing of project updates for a specific project.
     *
     * @param int $id The project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id)
    {
        try {
            // Find the project
            $project = Project::findOrFail($id);
            
            // Check if the authenticated user is the owner of the project
            $user = Auth::user();
            if (!$user) {
                // Log if user is not authenticated
                Log::warning('Unauthorized access attempt - User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view these updates.',
                ], 403);
            }

            // Check if the user is either the student or supervisor for the project
            if ($user->role === User::ROLE_STUDENT && $project->student_id !== $user->id) {
                Log::warning('Unauthorized access - Student not authorized for this project', [
                    'user_id' => $user->id,
                    'project_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view these updates.',
                ], 403);
            }

            if ($user->role === User::ROLE_SUPERVISOR && $project->supervisor_id !== $user->id) {
                Log::warning('Unauthorized access - Supervisor not authorized for this project', [
                    'user_id' => $user->id,
                    'project_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view these updates.',
                ], 403);
            }

            // Fetch project updates
            $updates = $project->projectUpdates()->with('author')->latest()->get();
            
            return response()->json([
                'success' => true,
                'data' => ProjectUpdateResource::collection($updates),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Project not found', [
                'error' => $e->getMessage(),
                'project_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve project updates', [
                'error' => $e->getMessage(),
                'project_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve project updates.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created project update.
     *
     * @param StoreProjectUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectUpdateRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Log validation success and request details for debugging
            Log::info('Project update validation passed', [
                'user_id' => Auth::id(),
                'project_id' => $validated['project_id'],
                'content_length' => strlen($validated['content'])
            ]);
            
            // Add the authenticated user's ID as the creator
            $validated['created_by'] = Auth::id();
            
            // Check if the authenticated user is the student or supervisor for the project
            $user = Auth::user();
            $project = Project::findOrFail($validated['project_id']);
            
            if ($user->role === User::ROLE_STUDENT && $project->student_id !== $user->id) {
                Log::warning('Unauthorized project update attempt - Student not allowed', [
                    'user_id' => $user->id,
                    'project_id' => $validated['project_id']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this project.',
                ], 403);
            }

            if ($user->role === User::ROLE_SUPERVISOR && $project->supervisor_id !== $user->id) {
                Log::warning('Unauthorized project update attempt - Supervisor not allowed', [
                    'user_id' => $user->id,
                    'project_id' => $validated['project_id']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this project.',
                ], 403);
            }

            // Create the project update
            $update = ProjectUpdate::create($validated);
            $update->load('author');
            
            return response()->json([
                'success' => true,
                'message' => 'Project update created successfully.',
                'data' => new ProjectUpdateResource($update),
            ], 201);
        } catch (\Exception $e) {
            // Log the detailed error
            Log::error('Failed to create project update', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project update.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Other methods (show, update, destroy) can be implemented similarly with appropriate role checks and logging.
}
