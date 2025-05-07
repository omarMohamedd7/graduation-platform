<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'content',
        'created_by',
    ];

    /**
     * Get the project this update belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user (student) who created this update
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
