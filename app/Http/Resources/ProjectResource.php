<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'proposal_id' => $this->proposal_id,
            
            // Include relationships
            'student' => new UserResource($this->whenLoaded('student')),
            'supervisor' => new UserResource($this->whenLoaded('supervisor')),
            'proposal' => new ProposalResource($this->whenLoaded('proposal')),
            
            // Timestamps
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
