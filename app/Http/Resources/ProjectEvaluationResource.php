<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectEvaluationResource extends JsonResource
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
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', function() {
                return [
                    'id' => $this->project->id,
                    'title' => $this->project->title,
                    'status' => $this->project->status
                ];
            }),
            'supervisor_id' => $this->supervisor_id,
            'supervisor' => $this->whenLoaded('supervisor', function() {
                return [
                    'id' => $this->supervisor->id,
                    'name' => $this->supervisor->full_name
                ];
            }),
            'committee_head_id' => $this->committee_head_id,
            'committee_head' => $this->whenLoaded('committeeHead', function() {
                return [
                    'id' => $this->committeeHead->id,
                    'name' => $this->committeeHead->full_name
                ];
            }),
            'supervisor_score' => $this->supervisor_score,
            'committee_score' => $this->committee_score,
            'final_score' => $this->final_score,
            'feedback' => $this->feedback,
            'status' => $this->status,
            'evaluated_at' => $this->evaluated_at ? $this->evaluated_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
} 