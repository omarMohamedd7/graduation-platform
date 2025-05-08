<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'proposal_id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'committee_feedback' => $this->committee_feedback,
            'supervisor_response' => $this->supervisor_response,
            
            // Include relationships using their respective resources
            'student' => new UserResource($this->whenLoaded('student')),
            'proposed_supervisor' => new UserResource($this->whenLoaded('proposedSupervisor')),
            'supervisor' => $this->when($this->supervisor_id, function() {
                return new UserResource($this->whenLoaded('supervisor'));
            }),
            
            // // Add helpful computed properties for frontend
            // 'has_proposed_supervisor' => (bool) $this->proposed_supervisor_id,
            // 'has_assigned_supervisor' => (bool) $this->supervisor_id,
            // 'needs_supervisor_assignment' => $this->status === 'APPROVED' && !$this->supervisor_id,
            // 'awaiting_supervisor_response' => $this->supervisor_id && $this->supervisor_response === 'PENDING',
            // 'is_in_progress' => $this->status === 'IN_PROGRESS',
            

        ];
    }
}
