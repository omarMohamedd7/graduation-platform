@props([
    'proposal',
    'project',
    'projectupdates'
])

<x-layout bodyClass="g-sidenav-show bg-gray-200">
<main class="main-content …">
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


  <div class="container-fluid py-2">

    @if($proposal && $proposal->status == 'APPROVED')

    <h3>Proposal</h3>
    <div class="card mb-2 p-2 small shadow-sm">
      <div class="card-header py-2 px-3">
        <h5 class="mb-0">{{ $proposal->title }}</h5>
      </div>
      <div class="card-body py-2 px-3">
        <p>
          <strong>Status:</strong>
          <span class="badge bg-success text-white">{{ $proposal->status }}</span>
        </p>
        @if($proposal->committee_feedback)
          <p><strong>Feedback:</strong> {{ $proposal->committee_feedback }}</p>
        @endif
      </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4>Project Updates</h4>
          <!-- Plus Icon Toggle -->
          <button class="btn btn-sm btn-dark" onclick="document.getElementById('update-form').classList.toggle('d-none')">
            ➕ Add Update
          </button>
        </div>
        <div class="card-body">

          {{-- 📋 Updates Table --}}
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Content</th>
                <th>Attachment</th>
                <th>Supervisor Notes</th>
                <th>Evaluation</th>
              </tr>
            </thead>
            <tbody>
              @forelse($projectupdates as $index => $update)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $update->content }}</td>
                  <td>
                    @if($update->attachment_path)
                      <a href="{{ Storage::url($update->attachment_path) }}" target="_blank">Download</a>
                    @else
                      N/A
                    @endif
                  </td>
                  <td>{{ $update->supervisor_notes ?? '—' }}</td>
                  <td>
                    @if($update->evaluation === 'AGREE')
                        <span class="text-success">AGREE</span>
                    @elseif($update->evaluation === 'DISAGREE')
                        <span class="text-danger">DISAGREE</span>
                    @else
                        <span>Pending</span>
                    @endif
                </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5">No updates submitted yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>

          <div id="update-form" class="mt-4 d-none">
            <form method="POST" action="{{ route('update_project') }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="project_id" value="{{$project->id}} ">
              <div class="mb-3">
                <label class="form-label">Update Content</label>
                <textarea name="content" class="form-control border border-dark" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Attachment (optional)</label>
                <input type="file" name="attachment" class="form-control border border-dark">
              </div>
              <button type="submit" class="btn btn-dark">Submit Update</button>
            </form>
          </div>

        </div>
      </div>


    @elseif($proposal && $proposal->status === 'REJECTED')

      <h3>Previous Proposal</h3>
      <div class="card mb-4">
        <div class="card-header">
          <h3>{{ $proposal->title }}</h3>
        </div>
        <div class="card-body">
            <p>
                <strong>Status:</strong>
                <span class="badge bg-danger text-white">{{ $proposal->status }}</span>
              </p>
            <p><strong>Description:</strong> {{ $proposal->description }}</p>
          @if($proposal->committee_feedback)
            <p><strong>Feedback:</strong> {{ $proposal->committee_feedback }}</p>
          @endif
        </div>
      </div>

      {{-- 📝 New Proposal Form --}}
      <div class="card mb-4">
        <div class="card-header"><h3>Resubmit Proposal</h3></div>
        <div class="card-body">
          <form method="POST" action="{{ route('add-proposal') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control border border-dark" value="{{ old('title') }}">
              @error('title') <p class="text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control border border-dark" rows="4">{{ old('description') }}</textarea>
              @error('description') <p class="text-danger">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn bg-gradient-dark">Submit Proposal</button>
          </form>
        </div>
      </div>
      @elseif($proposal )

      <h3>Proposal</h3>
      <div class="card mb-2 p-2 small shadow-sm">
      <div class="card-header py-2 px-3">
          <h5 class="mb-0">{{ $proposal->title }}</h5>
      </div>
      <div class="card-body py-2 px-3">
          <p>
          <strong>Status:</strong>
          <span class="text-black">{{ $proposal->status }}</span>
          </p>
          @if($proposal->committee_feedback)
          <p><strong>Feedback:</strong> {{ $proposal->committee_feedback }}</p>
          @endif
      </div>
      </div>

    @elseif(!$proposal)
      {{-- 🆕 No Proposal Yet: show form only --}}
      <h3>New Proposal</h3>
      <div class="card mb-4">
        <div class="card-header"><h3>Submit Proposal</h3></div>
        <div class="card-body">
          <form method="POST" action="{{ route('add-proposal') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control border border-dark" value="{{ old('title') }}">
              @error('title') <p class="text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control border border-dark" rows="4">{{ old('description') }}</textarea>
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
