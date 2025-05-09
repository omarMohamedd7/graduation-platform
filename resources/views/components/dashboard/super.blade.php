@props([

  'projects'
])
{{-- resources/views/dashboard/index.blade.php --}}
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
            @foreach ($projects as $project)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <h4>{{ $project->title }}</h4>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#updates-{{ $project->id }}">
                        Details
                    </button>
                </div>

                <div id="updates-{{ $project->id }}" class="collapse card-body">
                    @if($project->updates->isEmpty())
                        <p>No updates yet.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Content</th>
                                    <th>Attachment</th>
                                    <th>Supervisor Notes</th>
                                    <th>Evaluation</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project->updates as $update)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $update->content }}</td>
                                        <td>
                                            @if($update->attachment_path)
                                                <a href="{{ asset('storage/' . $update->attachment) }}" target="_blank">Download</a>
                                            @endif
                                        </td>
                                        <form method="POST" action="{{ route('update_evaluation') }}">
                                            @csrf
                                            <input type="hidden" name="update_id" value="{{ $update->id }}">
                                            <td>
                                                <input type="text" name="notes" class="form-control" value="{{ $update->supervisor_notes }}">
                                            </td>
                                            <td>
                                                <select name="evaluation" class="form-select">
                                                    <option value="">Select</option>
                                                    <option value="AGREE" {{ $update->evaluation === 'AGREE' ? 'selected' : '' }}>AGREE</option>
                                                    <option value="DISAGREE" {{ $update->evaluation === 'DISAGREE' ? 'selected' : '' }}>DISAGREE</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-sm btn-success">Save</button>
                                            </td>
                                        </form>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endforeach


    </main>
    <x-plugins />
  </x-layout>
