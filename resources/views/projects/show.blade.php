<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Project Details"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Project Details</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 py-4">
                            <h4>{{ $project->title }}</h4>
                            <p class="text-sm text-muted mb-4">
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $project->status === 'COMPLETED' ? 'success' : 'primary' }}">
                                    {{ $project->status }}
                                </span>
                            </p>
                            
                            <div class="mb-4">
                                <h6 class="mb-2">Description</h6>
                                <p>{{ $project->description }}</p>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="mb-2">Student</h6>
                                    <p>{{ $project->student->full_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">Supervisor</h6>
                                    <p>{{ $project->supervisor->full_name }}</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="mb-2">Timeline</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Start Date:</strong> {{ $project->start_date ? $project->start_date->format('d M Y') : 'Not set' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>End Date:</strong> {{ $project->end_date ? $project->end_date->format('d M Y') : 'Not set' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Duration:</strong> {{ $project->start_date && $project->end_date ? $project->start_date->diffInDays($project->end_date) . ' days' : 'Not determined' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                </a>
                                
                                <div>
                                    <a href="{{ route('projects.evaluation', $project->id) }}" class="btn bg-gradient-info">
                                        <i class="fas fa-chart-bar me-2"></i> View Evaluation
                                    </a>
                                    
                                    {{-- Add more actions as needed --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <x-document-list 
                        :documentableType="$documentableType"
                        :documentableId="$documentableId"
                        :documents="$documents"
                    />
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout> 