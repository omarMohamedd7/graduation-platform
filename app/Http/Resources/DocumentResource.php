<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
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
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'description' => $this->description,
            'uploaded_by' => $this->whenLoaded('uploader', function () {
                return [
                    'id' => $this->uploader->id,
                    'name' => $this->uploader->full_name,
                    'role' => $this->uploader->role,
                ];
            }),
            'documentable_type' => class_basename($this->documentable_type),
            'documentable_id' => $this->documentable_id,
            'uploaded_at' => $this->created_at->format('Y-m-d H:i:s'),
            'download_url' => route('documents.download', $this->id),
            'size' => Storage::disk('public')->exists($this->file_path)
                ? Storage::disk('public')->size($this->file_path)
                : null,
        ];
    }
} 