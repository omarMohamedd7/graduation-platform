<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProposalService
{
    /**
     * Store a new proposal
     *
     * @param array $data The validated proposal data
     * @return Proposal The created proposal
     */
    public function storeProposal(array $data): Proposal
{
    Log::info('Creating or updating proposal', [
        'user_id' => Auth::id(),
        'authenticated' => Auth::check(),
        'request_data' => $data
    ]);

    $user = Auth::user();
    $existingProposal = $user->proposal; // Assuming user has `proposal()` relationship defined

    $user_department = $user->department;
    $head = User::where('department', $user_department)
                ->where('role', User::ROLE_COMMITTEE_HEAD)
                ->first();

    if ($existingProposal) {
        // Update the existing proposal
        $existingProposal->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'PENDING',
            'department_head' => $head?->id,
        ]);
        return $existingProposal;
    } else {
        // Create a new proposal
        $proposal = Proposal::create([
            'student_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'department_head' => $head?->id,
            'status' => 'PENDING',
        ]);
        return $proposal;
    }
}


    /**
     * Get all proposals with related data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProposals()
    {
        return Proposal::with(['student', 'proposedSupervisor', 'supervisor'])->latest()->get();
    }

    /**
     * Get a single proposal by ID
     *
     * @param int $id The proposal ID
     * @return Proposal The proposal with related data
     * @throws ModelNotFoundException
     */
    public function getProposalById(int $id): Proposal
    {
        return Proposal::with(['student', 'proposedSupervisor', 'supervisor'])->findOrFail($id);
    }

    /**
     * Approve a proposal
     *
     * @param int $id The proposal ID
     * @param string|null $committeeFeedback Optional feedback from committee
     * @return Proposal The updated proposal
     * @throws ModelNotFoundException
     */
    public function approveProposal($data,): Proposal
    {

        $proposal = Proposal::findOrFail($data['proposal_id']);
        // dd($proposal);
        // Check if there's a proposed supervisor
        $updates = [
            'status' => $data['status'],
            'committee_feedback' => $data['feedback'] ?? null,

        ];


        // dd($updates);
        $proposal->update($updates);
        return $proposal;
    }

    /**
     * Reject a proposal
     *
     * @param int $id The proposal ID
     * @param string $committeeFeedback Feedback from committee
     * @return Proposal The updated proposal
     * @throws ModelNotFoundException
     */
    public function rejectProposal(int $id, string $committeeFeedback): Proposal
    {
        $proposal = Proposal::findOrFail($id);

        $proposal->update([
            'status' => 'REJECTED',
            'committee_feedback' => $committeeFeedback,
        ]);

        Log::info('Proposal rejected', [
            'proposal_id' => $proposal->id,
            'rejected_by' => Auth::id()
        ]);

        $proposal->load(['student', 'proposedSupervisor', 'supervisor']);

        return $proposal;
    }

    /**
     * Assign a supervisor to a proposal
     *
     * @param int $id The proposal ID
     * @param int $supervisorId The supervisor user ID
     * @return Proposal The updated proposal
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function assignSupervisor(int $id, int $supervisorId): Proposal
    {
        Log::info('Assigning supervisor to proposal', [
            'user_id' => Auth::id(),
            'proposal_id' => $id,
            'supervisor_id' => $supervisorId
        ]);

        // Verify supervisor exists and has the correct role
        $supervisor = User::findOrFail($supervisorId);

        if (!isset($supervisor->role) || $supervisor->role !== User::ROLE_SUPERVISOR) {
            throw new \Exception('The selected user is not a supervisor');
        }

        // Get the proposal
        $proposal = Proposal::findOrFail($id);

        // Make sure proposal is approved before assigning supervisor
        if ($proposal->status !== 'APPROVED') {
            throw new \Exception('Cannot assign supervisor to a proposal that is not approved');
        }

        // Update the proposal
        $proposal->update([
            'supervisor_id' => $supervisorId,
            'supervisor_response' => 'PENDING',
        ]);

        Log::info('Supervisor assigned successfully', [
            'proposal_id' => $proposal->id,
            'supervisor_id' => $supervisorId
        ]);

        $proposal->load(['student', 'proposedSupervisor', 'supervisor']);

        return $proposal;
    }

    /**
     * Handle supervisor response to assignment
     *
     * @param int $id The proposal ID
     * @param string $response The supervisor's response (ACCEPTED or DECLINED)
     * @return array The updated proposal and optionally the created project
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function respondToAssignment(int $id, string $response): array
    {
        Log::info('Processing supervisor response to assignment', [
            'user_id' => Auth::id(),
            'proposal_id' => $id,
            'response' => $response
        ]);

        $proposal = Proposal::with(['student', 'proposedSupervisor', 'supervisor'])->findOrFail($id);

        // Check if the proposal has a supervisor assigned
        if ($proposal->supervisor_id === null) {
            throw new \Exception('No supervisor assigned to this proposal');
        }

        // Check if the authenticated user is the assigned supervisor
        if (Auth::id() != $proposal->supervisor_id) {
            throw new \Exception('You are not the assigned supervisor for this proposal');
        }

        // Check if the proposal is already responded to
        if ($proposal->supervisor_response !== 'PENDING' && $proposal->supervisor_response !== null) {
            throw new \Exception('You have already responded to this assignment');
        }

        // Make sure proposal is approved
        if ($proposal->status !== 'APPROVED') {
            throw new \Exception('Cannot respond to a proposal that is not approved');
        }

        // Update the supervisor response
        $updateData = [
            'supervisor_response' => $response,
        ];

        // If accepted, change the status to IN_PROGRESS
        if ($response === 'ACCEPTED') {
            $updateData['status'] = 'IN_PROGRESS';
        }

        $proposal->update($updateData);

        Log::info('Updated supervisor response successfully', [
            'proposal_id' => $proposal->id,
            'response' => $response,
            'new_status' => $updateData['status'] ?? $proposal->status
        ]);

        $result = [
            'proposal' => $proposal->fresh(['student', 'proposedSupervisor', 'supervisor']),
            'project' => null
        ];

        // Handle acceptance - create project
        if ($response === 'ACCEPTED') {
            try {
                // Create project based on the proposal
                $project = Project::create([
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'student_id' => $proposal->student_id,
                    'supervisor_id' => $proposal->supervisor_id,
                    'proposal_id' => $proposal->id,
                    'status' => 'ACTIVE',
                ]);

                $project->load(['student', 'supervisor']);

                Log::info('Project created successfully', [
                    'project_id' => $project->id,
                    'proposal_id' => $proposal->id,
                    'student_id' => $proposal->student_id,
                    'supervisor_id' => $proposal->supervisor_id
                ]);

                $result['project'] = $project;
            } catch (\Exception $e) {
                Log::error('Failed to create project', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'proposal_id' => $proposal->id
                ]);

                // Try to rollback the acceptance
                $proposal->update([
                    'supervisor_response' => 'PENDING',
                    'status' => 'APPROVED'
                ]);

                throw new \Exception('Failed to create project after acceptance: ' . $e->getMessage());
            }
        }

        // Handle declination - unassign supervisor
        if ($response === 'DECLINED') {
            $proposal->update([
                'supervisor_id' => null,
                'supervisor_response' => null,
                // Keep the status as APPROVED - so committee can assign a different supervisor
            ]);

            Log::info('Supervisor declined and unassigned successfully', [
                'proposal_id' => $proposal->id,
                'former_supervisor_id' => Auth::id()
            ]);

            $result['proposal'] = $proposal->fresh(['student', 'proposedSupervisor', 'supervisor']);
        }

        return $result;
    }
}
