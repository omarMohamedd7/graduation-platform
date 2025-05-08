<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\User;
use App\Services\ProjectEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WebProjectEvaluationController extends Controller
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
     * Show project evaluation form and results
     */
    public function show(Project $project)
    {
        // Check if user has permission to view this project's evaluation
        if (Auth::id() != $project->student_id && 
            Auth::id() != $project->supervisor_id && 
            Auth::user()->role != User::ROLE_COMMITTEE_HEAD) {
            return redirect()->back()->with('error', 'You do not have permission to view this evaluation');
        }
        
        // Get the evaluation if it exists
        $evaluation = $this->evaluationService->getEvaluationForProject($project->id);
        
        // Get committee heads for dropdown if user is committee head
        $committeeHeads = [];
        if (Auth::user()->role == User::ROLE_COMMITTEE_HEAD) {
            $committeeHeads = User::where('role', User::ROLE_COMMITTEE_HEAD)->get();
        }
        
        return view('evaluations.show', [
            'project' => $project,
            'evaluation' => $evaluation,
            'committeeHeads' => $committeeHeads,
            'userRole' => Auth::user()->role
        ]);
    }

    /**
     * Show specific evaluation by ID
     */
    public function showById(ProjectEvaluation $evaluation)
    {
        // Get the associated project
        $project = $evaluation->project;
        
        // Check if user has permission to view this evaluation
        if (Auth::id() != $project->student_id && 
            Auth::id() != $project->supervisor_id && 
            Auth::user()->role != User::ROLE_COMMITTEE_HEAD) {
            return redirect()->back()->with('error', 'You do not have permission to view this evaluation');
        }
        
        return view('evaluations.show', [
            'project' => $project,
            'evaluation' => $evaluation,
            'userRole' => Auth::user()->role
        ]);
    }

    /**
     * Show evaluation initialization form
     */
    public function initializeForm()
    {
        // Only committee heads can access this page
        if (Auth::user()->role != User::ROLE_COMMITTEE_HEAD) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to initialize evaluations');
        }
        
        // Get all projects that don't have evaluations yet
        $projects = Project::whereDoesntHave('evaluation')->get();
        $committeeHeads = User::where('role', User::ROLE_COMMITTEE_HEAD)->get();
        
        return view('evaluations.initialize', [
            'projects' => $projects,
            'committeeHeads' => $committeeHeads
        ]);
    }

    /**
     * Initialize a new project evaluation
     */
    public function initialize(Request $request)
    {
        // Only committee heads can initialize evaluations
        if (Auth::user()->role != User::ROLE_COMMITTEE_HEAD) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to initialize evaluations');
        }
        
        // Validate input
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'committee_head_id' => 'required|exists:users,id'
        ]);
        
        try {
            // Initialize the evaluation
            $evaluation = $this->evaluationService->initializeEvaluation(
                $validated['project_id'],
                $validated['committee_head_id']
            );
            
            return redirect()->route('evaluations.show', $evaluation->id)
                ->with('success', 'Project evaluation initialized successfully');
        } catch (\Exception $e) {
            Log::error('Error initializing project evaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to initialize evaluation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Submit committee head's evaluation
     */
    public function submitEvaluation(Request $request)
    {
        // Only committee heads can submit evaluations
        if (Auth::user()->role != User::ROLE_COMMITTEE_HEAD) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to submit evaluations');
        }
        
        // Validate input
        $validated = $request->validate([
            'evaluation_id' => 'required|exists:project_evaluations,id',
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string'
        ]);
        
        try {
            // Submit the evaluation
            $evaluation = $this->evaluationService->submitEvaluation(
                $validated['evaluation_id'],
                $validated['score'],
                $validated['feedback'] ?? null
            );
            
            return redirect()->route('evaluations.show', $evaluation->id)
                ->with('success', 'Evaluation submitted successfully');
        } catch (\Exception $e) {
            Log::error('Error submitting evaluation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to submit evaluation: ' . $e->getMessage())
                ->withInput();
        }
    }
} 