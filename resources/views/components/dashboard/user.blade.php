@props([

  'proposal'
])
{{-- resources/views/dashboard/index.blade.php --}}
<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content …">


      <div class="container-fluid py-4">
        @if($proposal)
          {{-- — USER ALREADY HAS A PROPOSAL: show status --}}
          <div class="card mb-4">
            <div class="card-header">
              <h3>{{ $proposal->title }}</h3>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong> {{ $proposal->status }}</p>

              <p><strong>Description:</strong> {{ $proposal->description }}</p>
              @if($proposal->committee_feedback)
                <p><strong>Feedback:</strong> {{ $proposal->committee_feedback }}</p>
              @endif
            </div>
          </div>
        @else
          {{-- — NO PROPOSAL YET: show creation form --}}
          <div class="card mb-4">
            <div class="card-header"><h3>New Proposal</h3></div>
            <div class="card-body">
              <form method="POST" action="{{ route('add-proposal') }}">
                @csrf
                <div class="mb-3">
                  <label class="form-label">Title</label>
                  <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                  @error('title') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                  @error('description') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn bg-gradient-dark">Submit Proposal</button>
              </form>
            </div>
          </div>
        @endif
      </div>
    </main>
    <x-plugins />
  </x-layout>
