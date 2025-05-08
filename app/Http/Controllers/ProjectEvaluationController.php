<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommitteeEvaluationRequest;
use App\Http\Requests\InitializeEvaluationRequest;
use App\Http\Requests\SupervisorEvaluationRequest;
use App\Http\Resources\ProjectEvaluationResource;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Services\ProjectEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectEvaluationController extends Controller
{
    /**
     * @var \App\Services\ProjectEvaluationService
     */
    protected $evaluationService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\ProjectEvaluationService $evaluationService
     * @return void
     */
    public function __construct(ProjectEvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Format a standard API response
     */
    private function formatResponse($message, $data = null, $errors = null, $status = 200)
    {
        $response = [
            'success' => $status < 400,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Initialize a project evaluation
     */
    public function initialize(InitializeEvaluationRequest $request)
    {
        try {
            $evaluation = $this->evaluationService->initializeEvaluation(
                $request->input('project_id'),
                $request->input('committee_head_id')
            );

            return $this->formatResponse(
                'Project evaluation initialized successfully',
                new ProjectEvaluationResource($evaluation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Resource not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error initializing project evaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->formatResponse(
                'Failed to initialize project evaluation',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Submit supervisor's evaluation
     */
    public function submitSupervisorEvaluation(SupervisorEvaluationRequest $request)
    {
        try {
            $evaluation = $this->evaluationService->submitSupervisorEvaluation(
                $request->input('evaluation_id'),
                $request->input('supervisor_score')
            );

            return $this->formatResponse(
                'Supervisor evaluation submitted successfully',
                new ProjectEvaluationResource($evaluation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Evaluation not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error submitting supervisor evaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->formatResponse(
                'Failed to submit supervisor evaluation',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Submit committee's evaluation and finalize
     */
    public function submitCommitteeEvaluation(CommitteeEvaluationRequest $request)
    {
        try {
            $evaluation = $this->evaluationService->submitCommitteeEvaluation(
                $request->input('evaluation_id'),
                $request->input('committee_score'),
                $request->input('feedback')
            );

            return $this->formatResponse(
                'Committee evaluation submitted and finalized successfully',
                new ProjectEvaluationResource($evaluation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Evaluation not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error submitting committee evaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->formatResponse(
                'Failed to submit committee evaluation',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get evaluation for a project
     */
    public function getForProject(int $projectId)
    {
        try {
            // Verify the project exists
            $project = Project::findOrFail($projectId);
            
            // Check if user has permission to view this project's evaluation
            if (Auth::id() != $project->student_id && 
                Auth::id() != $project->supervisor_id && 
                Auth::user()->role != 'COMMITTEE_HEAD') {
                return $this->formatResponse(
                    'You do not have permission to view this project\'s evaluation',
                    null,
                    ['error' => 'Unauthorized'],
                    403
                );
            }
            
            $evaluation = $this->evaluationService->getEvaluationForProject($projectId);
            
            if (!$evaluation) {
                return $this->formatResponse(
                    'No evaluation found for this project',
                    null,
                    null,
                    404
                );
            }

            return $this->formatResponse(
                'Project evaluation retrieved successfully',
                new ProjectEvaluationResource($evaluation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Project not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving project evaluation', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);

            return $this->formatResponse(
                'Failed to retrieve project evaluation',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get an evaluation by ID
     */
    public function show(int $id)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($id);
            
            // Check if user has permission to view this evaluation
            $project = $evaluation->project;
            if (Auth::id() != $project->student_id && 
                Auth::id() != $project->supervisor_id && 
                Auth::user()->role != 'COMMITTEE_HEAD') {
                return $this->formatResponse(
                    'You do not have permission to view this evaluation',
                    null,
                    ['error' => 'Unauthorized'],
                    403
                );
            }

            return $this->formatResponse(
                'Evaluation retrieved successfully',
                new ProjectEvaluationResource($evaluation)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Evaluation not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving evaluation', [
                'error' => $e->getMessage(),
                'evaluation_id' => $id
            ]);

            return $this->formatResponse(
                'Failed to retrieve evaluation',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }
} 