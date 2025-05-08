<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * @var \App\Services\DocumentService
     */
    protected $documentService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\DocumentService $documentService
     * @return void
     */
    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display the project details
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function show(Project $project)
    {
        // Check if user has permission to view this project
        if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
            return redirect()->back()->with('error', 'You do not have permission to view this project');
        }

        // Get documents for the project
        $documents = $this->documentService->getDocumentsForModel($project);

        return view('projects.show', [
            'project' => $project,
            'documents' => $documents,
            'documentableType' => 'Project',
            'documentableId' => $project->id
        ]);
    }
} 