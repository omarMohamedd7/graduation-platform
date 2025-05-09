<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * User roles
     */
    public const ROLE_STUDENT = 'STUDENT';
    public const ROLE_SUPERVISOR = 'SUPERVISOR';
    public const ROLE_COMMITTEE_HEAD = 'COMMITTEE_HEAD';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'department',
    ];
    protected $table = 'users';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /**
     * Check if user is a supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    /**
     * Check if user is a committee head
     */
    public function getRelevantProposal()
    {
        $x = match($this->role) {
            User::ROLE_COMMITTEE_HEAD => Proposal::where('department_head', $this->id)->get(),
            User::ROLE_STUDENT => Proposal::where('student_id', $this->id)->first(),
            default => null,
            };
        dd($x);
}

    public function isCommitteeHead(): bool
    {
        return $this->role === self::ROLE_COMMITTEE_HEAD;
    }

    public function proposal()
    {
        if ($this->isCommitteeHead()){
            // dd($this->id);
            // dd(Proposal::where('department_head',2)->get());

            return $this->hasMany(Proposal::class, 'department_head');}
        else if ($this->isStudent())
            return $this->hasOne(Proposal::class, 'student_id');

    }
    public function project()
    {

        if ($this->isStudent())
            return $this->hasOne(Project::class, 'student_id');
        else if($this->isSupervisor())
            return $this->hasMany(Project::class, 'supervisor_id');
        return null;

    }
    public function getProjectsForCommitteeHead()
{
    if ($this->isCommitteeHead()) {
        $projects = Project::whereHas('student', function ($query) {
            $query->where('department', $this->department);
        })->get();

        return $projects;
    }
}
}
