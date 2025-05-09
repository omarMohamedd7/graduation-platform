@props([
'supervisors',
  'proposals',
  'projects'
])
@php
$activeCount = $projects->where('status', 'ACTIVE')->count();
$proposalPendingCount = $proposals->where('status', 'PENDING')->count();
$completedCount = $projects->where('status', 'COMPLETED')->count();
@endphp
{{-- resources/views/dashboard/index.blade.php --}}
<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content …">

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Active Projects</h5>
                    <span class="display-4">{{ $activeCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Pending Proposals</h5>
                    <span class="display-4">{{ $proposalPendingCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Completed Projects</h5>
                    <span class="display-4">{{ $completedCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                </ul>
            </div>
            @endif
      <div class="container-fluid py-4">
        @if($proposals->where('status', 'PENDING')->count() > 0)
            <h3>Proposals</h3>
        @endif
        @foreach($proposals as $proposal)
            @if($proposal->status == 'PENDING')
            <div class="card mb-4">
                <div class="card-header">
                <h3>{{ $proposal->title }}</h3>
                </div>
                <div class="card-body">


                <p><strong>Description:</strong> {{ $proposal->description }}</p>
                <form method="POST" action="{{ route('approve-proposal') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Assign Supervisor</label>
                        <select name="supervisor_id" class="form-select border border-dark">
                            <option value="">-- Select Supervisor --</option>
                            @foreach ($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}">
                                    {{ $supervisor->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_id') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-3">
                    <label class="form-label">Feed back:</label>
                    <input type="text" name="feedback" class="form-control border border-dark" value="{{ old('title') }}">

                    </div>
                    <input type="hidden" value="{{ $proposal->id }}" name='proposal_id'>
                    {{-- <input type="hidden" value="{{ $proposal}}" name='proposal'> --}}

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_approved" value="APPROVED"
                            {{ old('status', $proposal->status ?? '') === 'APPROVED' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_approved">
                            <span class="badge bg-success">Approved</span>
                        </label>
                        </div>

                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="status_declined" value="REJECTED"
                            {{ old('status', $proposal->status ?? '') === 'REJECTED' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_declined">
                            <span class="badge bg-danger">Declined</span>
                        </label>
                        </div>
                    </div>

                    <button type="submit" class="btn bg-gradient-dark">Submit</button>
                </form>
                </div>
            </div>
            @endif
        @endforeach
      </div>



      {{-- ------------------------- --}}
      <div class="container-fluid py-4">
        @if($projects->where('status', 'ACTIVE')->count() > 0)
            <h3>Projects</h3>
        @endif
        @foreach($projects as $project)
        @if($project->status == 'ACTIVE')
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $project->title }}</h3>
            </div>
            <div class="card-body">


                <form method="POST" action="{{ route('evaluate-project') }}">
                    @csrf

                    <!-- Mark input field aligned to the right -->
                    <div class="form-group text-right">
                        <label for="mark" class="form-label "><b>Mark:</b></label>
                        <input type="number" id="mark" name="mark" class="form-control w-auto d-inline-block  border border-dark" placeholder=" Enter mark" required>
                        <input type="hidden" value="{{ $project->id }}" name='project_id'>

                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn bg-gradient-dark mt-2">Submit</button>
                </form>
            </div>
        </div>
    @endif

        @endforeach
      </div>
    </main>
    <x-plugins />
  </x-layout>
