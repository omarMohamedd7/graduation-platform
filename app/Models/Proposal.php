<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'title',
        'description',
        'status',
        'committee_feedback',
        'department_head'
    ];

    protected $casts = [
        'status' => 'string',
        'supervisor_response' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function department_head()
    {
        return $this->belongsTo(User::class, 'department_head');
    }

}
