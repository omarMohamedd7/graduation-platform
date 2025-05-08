<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AssignSupervisorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $proposalId = $this->route('id');
        
        // Only committee heads can assign supervisors
        if (!$user || $user->role !== User::ROLE_COMMITTEE_HEAD) {
            Log::warning('Unauthorized supervisor assignment attempt', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'proposal_id' => $proposalId,
                'ip' => $this->ip()
            ]);
            
            return false;
        }
        
        Log::info('Supervisor assignment request authorized', [
            'user_id' => $user->id,
            'committee_head_name' => $user->full_name,
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
            'supervisor_id' => 'required|exists:users,id',
        ];
    }
    
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'supervisor_id.required' => 'A supervisor ID is required',
            'supervisor_id.exists' => 'The selected supervisor does not exist',
        ];
    }
    
    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Validate that the user has the SUPERVISOR role
        $supervisor = User::find($this->supervisor_id);
        
        if (!$supervisor || $supervisor->role !== User::ROLE_SUPERVISOR) {
            throw ValidationException::withMessages([
                'supervisor_id' => 'The selected user is not a supervisor',
            ]);
        }
    }
}
