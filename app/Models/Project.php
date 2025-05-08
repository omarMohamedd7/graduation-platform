<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'student_id',
        'supervisor_id',
        'proposal_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Get the updates for this project
     */
    public function projectUpdates()
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    /**
     * Get the supervisor notes for this project
     */
    public function supervisorNotes()
    {
        return $this->hasMany(SupervisorNote::class);
    }

    /**
     * Get all documents associated with this project
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the evaluation for this project
     */
    public function evaluation()
    {
        return $this->hasOne(ProjectEvaluation::class);
    }
}
