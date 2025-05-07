<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupervisorNoteRequest;
use App\Http\Resources\SupervisorNoteResource;
use App\Models\Project;
use App\Models\SupervisorNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupervisorNoteController extends Controller
{
    /**
     * Display a listing of supervisor notes for a specific project.
     *
     * @param int $id The project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id)
    {
        try {
            $project = Project::findOrFail($id);
            
            // Check if the authenticated user is the supervisor of the project
            $user = Auth::user();
            if (!$user || $project->supervisor_id !== $user->id) {
                Log::warning('Unauthorized access attempt to supervisor notes', [
                    'user_id' => $user ? $user->id : null,
                    'project_id' => $id,
                    'project_supervisor_id' => $project->supervisor_id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view these notes.',
                ], 403);
            }
            
            $notes = $project->supervisorNotes()->with('supervisor')->latest()->get();
            
            return response()->json([
                'success' => true,
                'data' => SupervisorNoteResource::collection($notes),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Supervisor notes - Project not found', [
                'project_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve supervisor notes', [
                'error' => $e->getMessage(),
                'project_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supervisor notes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created supervisor note.
     *
     * @param StoreSupervisorNoteRequest $request
     * @param int $id The project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSupervisorNoteRequest $request, $id)
    {
        try {
            $project = Project::findOrFail($id);
            
            // Log the store attempt
            Log::info('Supervisor note creation attempt', [
                'user_id' => Auth::id(),
                'project_id' => $id
            ]);
            
            $note = new SupervisorNote([
                'project_id' => $project->id,
                'content' => $request->content,
                'supervisor_id' => Auth::id(),
            ]);
            
            $note->save();
            
            // Load the supervisor relationship for the resource
            $note->load('supervisor');
            
            return response()->json([
                'success' => true,
                'message' => 'Supervisor note created successfully.',
                'data' => new SupervisorNoteResource($note),
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Supervisor note creation - Project not found', [
                'project_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to create supervisor note', [
                'error' => $e->getMessage(),
                'project_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create supervisor note.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
