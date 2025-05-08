@props([
    'project'
])

<div class="card mb-4">
    <div class="card-header pb-0">
        <h5 class="mb-0">Project Evaluation Status</h5>
    </div>
    <div class="card-body">
        @php
            $evaluation = $project->evaluation;
        @endphp
        
        @if(!$evaluation)
            <div class="alert alert-info" role="alert">
                This project has not been evaluated yet.
                @if(auth()->user()->role == 'COMMITTEE_HEAD')
                    <a href="{{ route('evaluations.initialize.form') }}" class="btn btn-sm btn-primary mt-2">
                        Initialize Evaluation
                    </a>
                @endif
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">Status: 
                        @if($evaluation->status == 'PENDING')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($evaluation->status == 'COMPLETED')
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </h6>
                    @if($evaluation->status == 'COMPLETED')
                        <p class="mb-0 text-sm">
                            Score: <strong>{{ $evaluation->score }}/100</strong>
                        </p>
                    @endif
                </div>
                <a href="{{ route('projects.evaluation', $project->id) }}" class="btn btn-sm bg-gradient-primary">
                    View Details
                </a>
            </div>
            
            @if($evaluation->status == 'PENDING' && auth()->id() == $evaluation->committee_head_id)
                <div class="alert alert-warning" role="alert">
                    You need to submit your evaluation for this project.
                    <a href="{{ route('projects.evaluation', $project->id) }}" class="btn btn-sm btn-primary mt-2">
                        Submit Evaluation
                    </a>
                </div>
            @endif
        @endif
    </div>
</div> 