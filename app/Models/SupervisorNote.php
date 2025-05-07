<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'content',
        'supervisor_id',
    ];

    /**
     * Get the project this note belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the supervisor who created this note
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
