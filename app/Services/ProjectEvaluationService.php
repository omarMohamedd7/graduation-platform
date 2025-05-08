<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectEvaluationService
{
    /**
     * Initialize a project evaluation
     *
     * @param int $projectId The project ID
     * @param int $committeeHeadId The committee head user ID
     * @return ProjectEvaluation The created project evaluation
     * @throws ModelNotFoundException
     */
    public function initializeEvaluation(int $projectId, int $committeeHeadId): ProjectEvaluation
    {
        $project = Project::findOrFail($projectId);
        $committeeHead = User::findOrFail($committeeHeadId);
        
        // Validate the committee head role
        if ($committeeHead->role !== User::ROLE_COMMITTEE_HEAD) {
            throw new \Exception('The selected user is not a committee head');
        }
        
        // Create evaluation record
        $evaluation = ProjectEvaluation::create([
            'project_id' => $projectId,
            'committee_head_id' => $committeeHeadId,
            'status' => 'PENDING',
        ]);
        
        Log::info('Project evaluation initialized', [
            'evaluation_id' => $evaluation->id,
            'project_id' => $projectId,
            'committee_head_id' => $committeeHeadId,
            'created_by' => Auth::id()
        ]);
        
        return $evaluation->load(['project', 'committeeHead']);
    }
    
    /**
     * Submit committee head's evaluation
     *
     * @param int $evaluationId The evaluation ID
     * @param float $score The score given by the committee head
     * @param string|null $feedback Optional feedback from the committee
     * @return ProjectEvaluation The updated project evaluation
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function submitEvaluation(int $evaluationId, float $score, ?string $feedback = null): ProjectEvaluation
    {
        $evaluation = ProjectEvaluation::findOrFail($evaluationId);
        
        // Check if the authenticated user is the assigned committee head
        if (Auth::id() != $evaluation->committee_head_id) {
            throw new \Exception('You are not the assigned committee head for this project');
        }
        
        // Validate score range (0-100)
        if ($score < 0 || $score > 100) {
            throw new \Exception('Score must be between 0 and 100');
        }
        
        // Update evaluation with committee head's score and finalize
        $evaluation->update([
            'score' => $score,
            'feedback' => $feedback,
            'evaluated_at' => now(),
            'status' => 'COMPLETED'
        ]);
        
        Log::info('Committee head submitted evaluation', [
            'evaluation_id' => $evaluation->id,
            'committee_head_id' => Auth::id(),
            'score' => $score
        ]);
        
        return $evaluation->fresh(['project', 'committeeHead']);
    }
    
    /**
     * Get an evaluation by ID
     *
     * @param int $id The evaluation ID
     * @return ProjectEvaluation
     * @throws ModelNotFoundException
     */
    public function getEvaluationById(int $id): ProjectEvaluation
    {
        return ProjectEvaluation::with(['project', 'committeeHead'])->findOrFail($id);
    }
    
    /**
     * Get all evaluations for a project
     *
     * @param int $projectId The project ID
     * @return ProjectEvaluation|null
     */
    public function getEvaluationForProject(int $projectId): ?ProjectEvaluation
    {
        return ProjectEvaluation::where('project_id', $projectId)
            ->with(['project', 'committeeHead'])
            ->first();
    }
} 