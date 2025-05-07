<?php

namespace App\Http\Requests;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SupervisorResponseRequest extends FormRequest
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
        
        // Ensure the authenticated user is the assigned supervisor for this proposal
        $proposal = Proposal::find($this->route('id'));
        if (!$proposal) {
            return false;
        }
        
        return Auth::id() == $proposal->supervisor_id;
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
        $proposal = Proposal::find($this->route('id'));
        
        // Check if the proposal has already been responded to
        if ($proposal && $proposal->supervisor_response !== 'PENDING' && $proposal->supervisor_response !== null) {
            throw ValidationException::withMessages([
                'response' => 'You have already responded to this assignment',
            ]);
        }
        
        // Make sure the proposal is approved
        if ($proposal && $proposal->status !== 'APPROVED') {
            throw ValidationException::withMessages([
                'response' => 'Cannot respond to a proposal that is not approved',
            ]);
        }
    }
} 