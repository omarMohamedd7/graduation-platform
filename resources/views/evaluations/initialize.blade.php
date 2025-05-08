<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Initialize Project Evaluation"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <!-- Initialize Evaluation Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Initialize New Project Evaluation</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if($projects->isEmpty())
                        <div class="alert alert-info" role="alert">
                            There are no projects available for evaluation initialization. 
                            All projects have already been initialized for evaluation.
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn bg-gradient-dark">Back to Dashboard</a>
                    @else
                        <form method="POST" action="{{ route('evaluations.initialize') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Select Project</label>
                                <select class="form-select" id="project_id" name="project_id" required>
                                    <option value="">-- Select a Project --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            {{ $project->title }} (Student: {{ $project->student->full_name }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose a project to evaluate</div>
                                @error('project_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="committee_head_id" class="form-label">Assign Committee Head</label>
                                <select class="form-select" id="committee_head_id" name="committee_head_id" required>
                                    <option value="">-- Select a Committee Head --</option>
                                    @foreach($committeeHeads as $committeeHead)
                                        <option value="{{ $committeeHead->id }}" {{ Auth::id() == $committeeHead->id ? 'selected' : '' }}>
                                            {{ $committeeHead->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select the committee head who will evaluate this project</div>
                                @error('committee_head_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn bg-gradient-primary">Initialize Evaluation</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout> 