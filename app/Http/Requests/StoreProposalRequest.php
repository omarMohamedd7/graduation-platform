<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        // Only students can create proposals
        if (!$user || $user->role !== User::ROLE_STUDENT) {
            Log::warning('Unauthorized proposal creation attempt', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'ip' => $this->ip()
            ]);
            
            return false;
        }
        
        Log::info('Proposal creation request authorized', [
            'user_id' => $user->id,
            'student_name' => $user->full_name
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'proposed_supervisor_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A proposal title is required',
            'title.max' => 'The title cannot exceed 255 characters',
            'description.required' => 'A detailed description is required',
            'proposed_supervisor_id.exists' => 'The selected supervisor does not exist',
        ];
    }
    
    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // If proposed supervisor ID is provided, validate that the user has the SUPERVISOR role
        if ($this->has('proposed_supervisor_id') && $this->filled('proposed_supervisor_id')) {
            $supervisor = User::find($this->proposed_supervisor_id);
            
            if (!$supervisor || $supervisor->role !== User::ROLE_SUPERVISOR) {
                Log::warning('Invalid supervisor role specified in proposal', [
                    'proposed_supervisor_id' => $this->proposed_supervisor_id,
                    'actual_role' => $supervisor ? $supervisor->role : 'user not found',
                    'user_id' => Auth::id()
                ]);
                
                throw ValidationException::withMessages([
                    'proposed_supervisor_id' => 'The selected user is not a supervisor',
                ]);
            }
        }
    }
}
