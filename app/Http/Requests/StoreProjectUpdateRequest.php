<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreProjectUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only students who own the project can create updates
        try {
            $user = Auth::user();

            // Log the authorization attempt
            Log::info('Project update authorization attempt', [
                'user_id' => $user ? $user->id : null,
                'role' => $user ? $user->role : null,
                'project_id' => $this->input('project_id') ?? $this->route('id')
            ]);

            if (!$user) {
                Log::warning('Project update authorization failed: No authenticated user');
                return false;
            }

            if ($user->role !== User::ROLE_STUDENT) {
                Log::warning('Project update authorization failed: User is not a student', [
                    'actual_role' => $user->role
                ]);
                return false;
            }

            // If project_id is in the path parameters, use that
            $projectId = $this->route('id') ?? $this->input('project_id');

            if (!$projectId) {
                Log::warning('Project update authorization failed: No project ID provided');
                return false;
            }

            $project = Project::find($projectId);

            if (!$project) {
                Log::warning('Project update authorization failed: Project not found', [
                    'project_id' => $projectId
                ]);
                return false;
            }

            $isOwner = $project->student_id === $user->id;

            if (!$isOwner) {
                Log::warning('Project update authorization failed: User is not the project owner', [
                    'user_id' => $user->id,
                    'project_owner_id' => $project->student_id
                ]);
            }

            return $isOwner;
        } catch (\Exception $e) {
            Log::error('Error during project update authorization', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
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
        'content' => 'required|string|max:5000',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:20480', // Max 20MB


        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If project_id is in the route parameter, add it to the request
        if ($this->route('id') && !$this->has('project_id')) {
            $this->merge([
                'project_id' => $this->route('id'),
            ]);
        }
    }
}
