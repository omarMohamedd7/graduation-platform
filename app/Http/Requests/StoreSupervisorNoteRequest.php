<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreSupervisorNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        try {
            // Only supervisors assigned to the project can create notes
            $user = Auth::user();
            
            // Log the authorization attempt
            Log::info('Supervisor note authorization attempt', [
                'user_id' => $user ? $user->id : null,
                'role' => $user ? $user->role : null,
                'project_id' => $this->route('id')
            ]);
            
            if (!$user) {
                Log::warning('Supervisor note authorization failed: No authenticated user');
                return false;
            }
            
            if ($user->role !== User::ROLE_SUPERVISOR) {
                Log::warning('Supervisor note authorization failed: User is not a supervisor', [
                    'actual_role' => $user->role
                ]);
                return false;
            }
            
            // Get project ID from route parameter
            $projectId = $this->route('id');
            
            if (!$projectId) {
                Log::warning('Supervisor note authorization failed: No project ID provided');
                return false;
            }

            $project = Project::find($projectId);
            
            if (!$project) {
                Log::warning('Supervisor note authorization failed: Project not found', [
                    'project_id' => $projectId
                ]);
                return false;
            }
            
            $isSupervisor = $project->supervisor_id === $user->id;
            
            if (!$isSupervisor) {
                Log::warning('Supervisor note authorization failed: User is not the project supervisor', [
                    'user_id' => $user->id,
                    'project_supervisor_id' => $project->supervisor_id
                ]);
            }
            
            return $isSupervisor;
        } catch (\Exception $e) {
            Log::error('Error during supervisor note authorization', [
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
            'content' => 'required|string|max:10000',
        ];
    }
}
