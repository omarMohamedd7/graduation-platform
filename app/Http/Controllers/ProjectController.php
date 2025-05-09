<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function evaluate(Request $request)
    {
        try{
            // dd($request);
            $request->validate([
            'mark' => 'required|integer|min:0|max:100',
            'project_id' => 'required|exists:projects,id'
        ], [
            'mark.required' => 'Please enter a mark.',
            'mark.integer' => 'The mark must be a number.',
            'mark.min' => 'The mark cannot be less than 0.',
            'mark.max' => 'The mark cannot be greater than 100.',
        ]);

            $project= Project::find($request->project_id);
            $project->mark = $request->mark;
            $project->save();

            // Optional: Redirect or return response
            return redirect()->back()->with('success', 'Mark updated successfully.');
        }
        catch (ValidationException $e) {
        // Let Laravel handle it normally (redirect back with errors)
        throw $e;
        }
         catch (\Exception $e) {
            dd($e);
            return  response()->view('errors.500');
         }

    }




}
