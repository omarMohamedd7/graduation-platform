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


    public function store(StoreProjectUpdateRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = Auth::user();
            $project = Project::findOrFail($validated['project_id']);

            if ($user->role === User::ROLE_STUDENT && $project->student_id !== $user->id) {
                return redirect()->back();
            }

            if ($user->role === User::ROLE_SUPERVISOR && $project->supervisor_id !== $user->id) {
                 return redirect()->back();
            }
            $filePath = null;
            if ($request->hasFile('attachment')) {
                $filePath = $request->file('attachment')->store('project_updates', 'public');
            }

            ProjectUpdate::create([
                'project_id' => $validated['project_id'],
                'content' => $validated['content'],
                'attachment_path' => $filePath, // null if no file uploaded
            ]);
            return redirect()->back()->with('success', 'Update submitted successfully.');


        } catch (\Exception $e) {
            // Log the detailed error
            Log::error('Failed to create project update', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to add update ');
        }
    }
    public function updateEvaluation(Request $request)
{
    // dd($request);
    // Validate request input
    $validated = $request->validate([
        'notes' => 'nullable|string',
        'evaluation' => 'required|in:AGREE,DISAGREE',
        'update_id' => 'required|exists:project_updates,id'
    ]);
    // Find the update
    $update = ProjectUpdate::findOrFail($validated['update_id']);

    // Check if the current user is the supervisor of the associated project
    $user = Auth::user();
    if ($user->role !== User::ROLE_SUPERVISOR || $update->project->supervisor_id !== $user->id) {
        return redirect()->back()->with('error', 'Unauthorized access.');
    }
    // Update the evaluation
    $update->supervisor_notes = $validated['notes'];
    $update->evaluation = $validated['evaluation'];
    $update->save();

    return redirect()->back()->with('success', 'Evaluation updated successfully.');
}


}
