<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Project Evaluation"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <!-- Project Information Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Project: {{ $project->title }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong> {{ $project->description }}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Student:</strong> {{ $project->student->full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Supervisor:</strong> {{ $project->supervisor->full_name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Evaluation Status</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if(!$evaluation)
                        <div class="alert alert-info" role="alert">
                            This project has not been evaluated yet.
                            @if($userRole == 'COMMITTEE_HEAD')
                                <a href="{{ route('evaluations.initialize.form') }}" class="btn btn-sm btn-primary mt-3">
                                    Initialize Evaluation
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    @if($evaluation->status == 'PENDING')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($evaluation->status == 'COMPLETED')
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </p>
                                <p><strong>Committee Head:</strong> {{ $evaluation->committeeHead->full_name }}</p>
                                <p><strong>Evaluation Date:</strong> 
                                    {{ $evaluation->evaluated_at ? $evaluation->evaluated_at->format('F d, Y') : 'Not completed' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Evaluation Details</h6>
                                        <div class="mb-0">
                                            <span class="me-2">Score:</span>
                                            @if($evaluation->score !== null)
                                                <strong>{{ $evaluation->score }}</strong>/100
                                            @else
                                                <span class="text-muted">Not submitted</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($evaluation->feedback)
                            <div class="mt-4">
                                <h6>Committee Feedback</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $evaluation->feedback }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Committee Head Form (if applicable) -->
                        @if($userRole == 'COMMITTEE_HEAD' && $evaluation->status == 'PENDING' && $evaluation->committee_head_id == Auth::id())
                            <div class="mt-4">
                                <h6>Submit Evaluation</h6>
                                <form method="POST" action="{{ route('evaluations.submit') }}">
                                    @csrf
                                    <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">
                                    
                                    <div class="mb-3">
                                        <label for="score" class="form-label">Score (0-100)</label>
                                        <input type="number" class="form-control" id="score" name="score" 
                                            min="0" max="100" step="0.1" required>
                                        <div class="form-text">Enter a score between 0 and 100 for this project.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="feedback" class="form-label">Feedback (Optional)</label>
                                        <textarea class="form-control" id="feedback" name="feedback" rows="4"></textarea>
                                        <div class="form-text">Provide feedback about the project's strengths and weaknesses.</div>
                                    </div>
                                    
                                    <button type="submit" class="btn bg-gradient-primary">Submit Evaluation</button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout> 