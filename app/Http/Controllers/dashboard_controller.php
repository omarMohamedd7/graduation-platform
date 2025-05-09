<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class dashboard_controller extends Controller
{
    public function index(Request $request)
    {
        // load the one‑to‑one proposal relationship
        $proposal_content =NULL;
        $project_updates =NULL;
        if($request->user()->role != User::ROLE_SUPERVISOR)
            $proposal_content = $request->user()->proposal;
        $supervisors = User::where( 'department',$request->user()->department)->where('role',User::ROLE_SUPERVISOR)->get();
        if($request->user()->role ==User::ROLE_COMMITTEE_HEAD)
            $projects_content = $request->user()->getProjectsForCommitteeHead();
        else
            $projects_content = $request->user()->project;
        if($request->user()->role == User::ROLE_STUDENT && $projects_content !=NULL )
            $project_updates = ProjectUpdate::where('project_id', operator: $request->user()->project->id)->get();

        return view('dashboard.index', compact('proposal_content', 'supervisors','projects_content','project_updates'));



    }
}
