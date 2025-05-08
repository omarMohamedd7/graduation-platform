<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
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
     * Display a listing of documents for a project
     */
    public function index(Project $project)
    {
        // Check if user has permission to view documents for this project
        if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
            return redirect()->back()->with('error', 'You do not have permission to view documents for this project');
        }

        $documents = $this->documentService->getDocumentsForModel($project);
        
        return view('documents.index', [
            'documents' => $documents,
            'project' => $project,
            'documentableType' => 'Project',
            'documentableId' => $project->id
        ]);
    }

    /**
     * Display a form to upload a new document
     */
    public function create(Request $request)
    {
        $documentableType = $request->input('documentable_type');
        $documentableId = $request->input('documentable_id');
        
        // Validate and get the model
        if ($documentableType === 'Project') {
            $model = Project::findOrFail($documentableId);
            
            // Check permissions
            if (Auth::id() != $model->student_id && Auth::id() != $model->supervisor_id) {
                return redirect()->back()->with('error', 'You do not have permission to upload documents for this project');
            }
            
            $returnRoute = 'documents.project.index';
            $returnRouteParams = ['project' => $model->id];
        } else if ($documentableType === 'ProjectUpdate') {
            $model = ProjectUpdate::findOrFail($documentableId);
            $project = $model->project;
            
            // Check permissions
            if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
                return redirect()->back()->with('error', 'You do not have permission to upload documents for this project update');
            }
            
            $returnRoute = 'documents.project-update.index';
            $returnRouteParams = ['projectUpdate' => $model->id];
        } else {
            return redirect()->back()->with('error', 'Invalid document type');
        }
        
        return view('documents.create', [
            'documentableType' => $documentableType,
            'documentableId' => $documentableId,
            'model' => $model,
            'returnRoute' => $returnRoute,
            'returnRouteParams' => $returnRouteParams
        ]);
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        try {
            $documentableType = $request->input('documentable_type');
            $documentableId = $request->input('documentable_id');
            
            if ($documentableType === 'Project') {
                $model = Project::findOrFail($documentableId);
                $redirectRoute = 'documents.project.index';
                $redirectParams = ['project' => $model->id];
                
                // Check permissions
                if (Auth::id() != $model->student_id && Auth::id() != $model->supervisor_id) {
                    return redirect()->back()->with('error', 'You do not have permission to upload documents for this project');
                }
            } else if ($documentableType === 'ProjectUpdate') {
                $model = ProjectUpdate::findOrFail($documentableId);
                $redirectRoute = 'documents.project-update.index';
                $redirectParams = ['projectUpdate' => $model->id];
                
                $project = $model->project;
                // Check permissions
                if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
                    return redirect()->back()->with('error', 'You do not have permission to upload documents for this project update');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid document type');
            }
            
            $document = $this->documentService->storeDocument(
                $request->file('document'),
                $model,
                $request->input('description')
            );
            
            return redirect()->route($redirectRoute, $redirectParams)
                ->with('success', 'Document uploaded successfully');
        } catch (\Exception $e) {
            Log::error('Error uploading document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to upload document: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display documents for a project update
     */
    public function indexForProjectUpdate(ProjectUpdate $projectUpdate)
    {
        $project = $projectUpdate->project;
        
        // Check if user has permission to view documents for this project update
        if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
            return redirect()->back()->with('error', 'You do not have permission to view documents for this project update');
        }
        
        $documents = $this->documentService->getDocumentsForModel($projectUpdate);
        
        return view('documents.index', [
            'documents' => $documents,
            'project' => $project,
            'projectUpdate' => $projectUpdate,
            'documentableType' => 'ProjectUpdate',
            'documentableId' => $projectUpdate->id
        ]);
    }

    /**
     * Download a document
     */
    public function download(Document $document): BinaryFileResponse
    {
        try {
            // Check if user has permission to download this document
            $documentable = $document->documentable;
            
            if ($documentable instanceof Project) {
                $project = $documentable;
            } elseif ($documentable instanceof ProjectUpdate) {
                $project = $documentable->project;
            } else {
                throw new \Exception('Invalid document type');
            }
            
            if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
                abort(403, 'You do not have permission to download this document');
            }

            if (!Storage::disk('public')->exists($document->file_path)) {
                abort(404, 'Document file not found');
            }

            return response()->download(
                storage_path('app/public/' . $document->file_path),
                $document->file_name,
                ['Content-Type' => $document->file_type]
            );
        } catch (\Exception $e) {
            Log::error('Error downloading document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id
            ]);
            
            abort(500, 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function destroy(Document $document)
    {
        try {
            // Check if user has permission to delete this document
            $documentable = $document->documentable;
            
            if ($documentable instanceof Project) {
                $project = $documentable;
                $redirectRoute = 'documents.project.index';
                $redirectParams = ['project' => $project->id];
            } elseif ($documentable instanceof ProjectUpdate) {
                $project = $documentable->project;
                $redirectRoute = 'documents.project-update.index';
                $redirectParams = ['projectUpdate' => $documentable->id];
            } else {
                throw new \Exception('Invalid document type');
            }
            
            if (Auth::id() != $project->student_id && Auth::id() != $project->supervisor_id) {
                return redirect()->back()->with('error', 'You do not have permission to delete this document');
            }

            $this->documentService->deleteDocument($document);

            return redirect()->route($redirectRoute, $redirectParams)
                ->with('success', 'Document deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }
} 