<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApproveProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $proposalId = $this->route('id');

        // Only committee heads can approve proposals
        if (!$user || $user->role !== User::ROLE_COMMITTEE_HEAD) {
            Log::warning('Unauthorized proposal approval attempt', [
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'proposal_id' => $proposalId,
                'ip' => $this->ip()
            ]);

            return false;
        }

        Log::info('Proposal approval request authorized', [
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
            'feedback' => 'nullable|string',
            'status' => 'required|string',
            'proposal_id'=> 'required',


            'supervisor_id' => 'required_if:status,APPROVED'

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'committee_feedback.string' => 'Feedback must be a text string',
        ];
    }
}
