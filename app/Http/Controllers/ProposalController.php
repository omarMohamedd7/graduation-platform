<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProposalResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreProposalRequest;
use App\Http\Requests\RejectProposalRequest;
use App\Http\Requests\ApproveProposalRequest;
use App\Http\Requests\AssignSupervisorRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SupervisorResponseRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProposalController extends Controller
{
    /**
     * @var \App\Services\ProposalService
     */
    protected $proposalService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\ProposalService $proposalService
     * @return void
     */
    public function __construct(ProposalService $proposalService)
    {
        $this->proposalService = $proposalService;
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

    // STUDENT: Submit a new proposal
    public function store(StoreProposalRequest $request)
    {
        try {

            $proposal = $this->proposalService->storeProposal($request->validated());
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error creating proposal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.500', [], 500);
    }
    }

    // COMMITTEE_HEAD : View all proposals
    public function index()
    {
        try {
            $proposals = $this->proposalService->getAllProposals();
            return $this->formatResponse(
                'Proposals retrieved successfully',
                ProposalResource::collection($proposals)
            );
        } catch (\Exception $e) {
            Log::error('Error fetching proposals', [
                'error' => $e->getMessage()
            ]);
            return $this->formatResponse(
                'Failed to fetch proposals',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    // COMMITTEE_HEAD or SUPERVISOR: View a single proposal
    public function show($id)
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);
            return $this->formatResponse(
                'Proposal retrieved successfully',
                new ProposalResource($proposal)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Proposal not found',
                null,
                ['proposal_id' => 'The requested proposal does not exist'],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error fetching proposal', [
                'error' => $e->getMessage(),
                'proposal_id' => $id
            ]);
            return $this->formatResponse(
                'Failed to fetch proposal',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    // COMMITTEE_HEAD: Approve a proposal
    public function approve(ApproveProposalRequest $request)
    {

        try {
            $data = $request->validated();
            $this->proposalService->approveProposal($data);

            if($request->status == 'APPROVED'){

                $proposal = Proposal::find($data['proposal_id']);
                Project::create([
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'student_id' => $proposal->student_id,
                    'supervisor_id' => $data['supervisor_id'],
                    'proposal_id' => $proposal->id
                ]);
                // dd('asdasd');
            }
            return redirect()->back();

        }
        catch (\Exception $e) {
             response()->view('errors.500');
        }
    }



    // COMMITTEE_HEAD: Assign supervisor to a proposal
    public function assignSupervisor(AssignSupervisorRequest $request, $id)
    {
        try {
            $proposal = $this->proposalService->assignSupervisor($id, $request->validated()['supervisor_id']);

            return $this->formatResponse(
                'Supervisor assigned successfully',
                new ProposalResource($proposal)
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                $e->getModel() === User::class
                    ? 'Supervisor not found'
                    : 'Proposal not found',
                null,
                ['error' => $e->getMessage()],
                404
            );
        } catch (\Exception $e) {
            Log::error('Error assigning supervisor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'proposal_id' => $id
            ]);
            return $this->formatResponse(
                'Error assigning supervisor',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    // SUPERVISOR: Respond to assignment (accept or decline)
    public function respondAssignment(SupervisorResponseRequest $request, $id)
    {
        try {
            $result = $this->proposalService->respondToAssignment($id, $request->validated()['response']);

            if ($request->input('response') === 'ACCEPTED') {
                return $this->formatResponse(
                    'Assignment accepted, project created, and proposal moved to IN_PROGRESS',
                    [
                        'proposal' => new ProposalResource($result['proposal']),
                        'project' => new ProjectResource($result['project'])
                    ]
                );
            } else if ($request->input('response') === 'DECLINED') {
                return $this->formatResponse(
                    'Assignment declined successfully. Committee will need to assign a new supervisor.',
                    new ProposalResource($result['proposal'])
                );
            }

            // Default success response if we get here
            return $this->formatResponse(
                'Response recorded successfully',
                new ProposalResource($result['proposal'])
            );
        } catch (ModelNotFoundException $e) {
            return $this->formatResponse(
                'Proposal not found',
                null,
                ['proposal_id' => 'The specified proposal does not exist in the system'],
                404
            );
        } catch (\Exception $e) {
            Log::error('Unexpected error in supervisor response', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'proposal_id' => $id ?? 'unknown'
            ]);
            return $this->formatResponse(
                'An unexpected error occurred while processing your response',
                null,
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
