<?php

namespace App\Http\Requests;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RespondToAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only supervisors can respond to assignments
        if (!Auth::check() || Auth::user()->role !== User::ROLE_SUPERVISOR) {
            return false;
        }
        
        // Additionally, check if the user is the assigned supervisor for this proposal
        $proposalId = $this->route('id');
        $proposal = Proposal::find($proposalId);
        
        // If proposal doesn't exist or user is not the assigned supervisor, reject
        if (!$proposal || $proposal->supervisor_id !== Auth::id()) {
            return false;
        }
        
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
            throw ValidationException::withMessages([
                'proposal_id' => 'Proposal not found',
            ]);
        }
        
        if ($proposal->status !== 'APPROVED') {
            throw ValidationException::withMessages([
                'proposal_id' => 'Cannot respond to a proposal that is not approved',
            ]);
        }
        
        if ($proposal->supervisor_response !== 'PENDING') {
            throw ValidationException::withMessages([
                'response' => 'You have already responded to this assignment',
            ]);
        }
    }
}
