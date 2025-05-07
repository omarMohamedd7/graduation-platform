<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApproveProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only committee heads can approve proposals
        return Auth::check() && Auth::user()->role === User::ROLE_COMMITTEE_HEAD;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'committee_feedback' => 'nullable|string',
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
