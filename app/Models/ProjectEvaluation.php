<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'committee_head_id',
        'score',
        'feedback',
        'evaluated_at',
        'status',
    ];

    protected $casts = [
        'score' => 'float',
        'evaluated_at' => 'datetime',
    ];

    /**
     * Get the project associated with this evaluation.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the committee head associated with this evaluation.
     */
    public function committeeHead()
    {
        return $this->belongsTo(User::class, 'committee_head_id');
    }
} 