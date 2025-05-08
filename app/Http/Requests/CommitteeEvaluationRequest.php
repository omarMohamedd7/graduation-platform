<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommitteeEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        // Only committee heads can submit committee evaluations
        if (!$user || $user->role !== User::ROLE_COMMITTEE_HEAD) {
            Log::warning('Unauthorized committee evaluation attempt', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'ip' => $this->ip()
            ]);
            
            return false;
        }
        
        Log::info('Committee evaluation request authorized', [
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
            'evaluation_id' => 'required|exists:project_evaluations,id',
            'committee_score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
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
            'evaluation_id.required' => 'An evaluation ID is required',
            'evaluation_id.exists' => 'The selected evaluation does not exist',
            'committee_score.required' => 'A score is required',
            'committee_score.numeric' => 'The score must be a number',
            'committee_score.min' => 'The score must be at least 0',
            'committee_score.max' => 'The score cannot be greater than 100',
        ];
    }
} 