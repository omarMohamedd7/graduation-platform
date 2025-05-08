<?php

namespace App\Http\Requests;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RespondToAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $proposalId = $this->route('id');
        
        // Only supervisors can respond to assignments
        if (!$user || $user->role !== User::ROLE_SUPERVISOR) {
            Log::warning('Unauthorized assignment response attempt - Not a supervisor', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'proposal_id' => $proposalId,
                'ip' => $this->ip()
            ]);
            
            return false;
        }
        
        // Additionally, check if the user is the assigned supervisor for this proposal
        $proposal = Proposal::find($proposalId);
        
        // If proposal doesn't exist or user is not the assigned supervisor, reject
        if (!$proposal) {
            Log::warning('Assignment response for non-existent proposal', [
                'user_id' => $user->id,
                'proposal_id' => $proposalId
            ]);
            
            return false;
        }
        
        if ($proposal->supervisor_id !== $user->id) {
            Log::warning('Unauthorized assignment response - Not the assigned supervisor', [
                'user_id' => $user->id,
                'proposal_id' => $proposalId,
                'assigned_supervisor_id' => $proposal->supervisor_id
            ]);
            
            return false;
        }
        
        Log::info('Assignment response request authorized', [
            'user_id' => $user->id,
            'supervisor_name' => $user->full_name,
            'proposal_id' => $proposalId
        ]);
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'response' => 'required|in:ACCEPTED,DECLINED',
        ];
    }
    
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'response.required' => 'A response is required',
            'response.in' => 'Response must be either ACCEPTED or DECLINED',
        ];
    }
    
    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $proposalId = $this->route('id');
        $proposal = Proposal::find($proposalId);
        
        // Check if the proposal is in the correct state for response
        if (!$proposal) {
            Log::error('Proposal not found during validation', [
                'proposal_id' => $proposalId,
                'user_id' => Auth::id()
            ]);
            
            throw ValidationException::withMessages([
                'proposal_id' => 'Proposal not found',
            ]);
        }
        
        if ($proposal->status !== 'APPROVED') {
            Log::warning('Attempt to respond to non-approved proposal', [
                'proposal_id' => $proposalId,
                'current_status' => $proposal->status,
                'user_id' => Auth::id()
            ]);
            
            throw ValidationException::withMessages([
                'proposal_id' => 'Cannot respond to a proposal that is not approved',
            ]);
        }
        
        if ($proposal->supervisor_response !== 'PENDING') {
            Log::warning('Attempt to respond again to already processed assignment', [
                'proposal_id' => $proposalId,
                'current_response' => $proposal->supervisor_response,
                'user_id' => Auth::id()
            ]);
            
            throw ValidationException::withMessages([
                'response' => 'You have already responded to this assignment',
            ]);
        }
    }
}
