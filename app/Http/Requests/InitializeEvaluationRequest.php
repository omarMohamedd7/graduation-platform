<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InitializeEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        // Only committee heads can initialize evaluations
        if (!$user || $user->role !== User::ROLE_COMMITTEE_HEAD) {
            Log::warning('Unauthorized evaluation initialization attempt', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'ip' => $this->ip()
            ]);
            
            return false;
        }
        
        Log::info('Evaluation initialization request authorized', [
            'user_id' => $user->id,
            'committee_head_name' => $user->full_name
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
            'project_id' => 'required|exists:projects,id',
            'committee_head_id' => 'required|exists:users,id',
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
            'project_id.required' => 'A project ID is required',
            'project_id.exists' => 'The selected project does not exist',
            'committee_head_id.required' => 'A committee head ID is required',
            'committee_head_id.exists' => 'The selected committee head does not exist',
        ];
    }
} 