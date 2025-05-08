<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Documents"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">
                                    {{ isset($projectUpdate) ? 'Documents for Update: ' . $projectUpdate->title : 'Documents for Project: ' . $project->title }}
                                </h6>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5>Manage Documents</h5>
                                    <a href="{{ route('documents.create', [
                                        'documentable_type' => $documentableType,
                                        'documentable_id' => $documentableId
                                    ]) }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Upload New Document
                                    </a>
                                </div>

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

                                @if($documents->isEmpty())
                                    <div class="alert alert-info">
                                        No documents have been uploaded yet.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Document</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Uploaded By</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                                    <th class="text-secondary opacity-7"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($documents as $document)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex px-2 py-1">
                                                                <div>
                                                                    <i class="fas fa-file text-primary text-lg me-3"></i>
                                                                </div>
                                                                <div class="d-flex flex-column justify-content-center">
                                                                    <h6 class="mb-0 text-sm">{{ $document->file_name }}</h6>
                                                                    @if($document->description)
                                                                        <p class="text-xs text-secondary mb-0">{{ $document->description }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $document->uploader->full_name }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-xs font-weight-bold mb-0">{{ $document->created_at->format('d M Y') }}</p>
                                                            <p class="text-xs text-secondary mb-0">{{ $document->created_at->format('H:i') }}</p>
                                                        </td>
                                                        <td class="align-middle">
                                                            <div class="d-flex">
                                                                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-link text-dark px-3 mb-0">
                                                                    <i class="fas fa-download text-dark me-2"></i>Download
                                                                </a>
                                                                
                                                                <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0" 
                                                                        onclick="return confirm('Are you sure you want to delete this document?');">
                                                                        <i class="fas fa-trash text-danger me-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                
                                <div class="mt-4">
                                    <a href="{{ $documentableType === 'Project' ? route('dashboard') : route('documents.project.index', ['project' => $project->id]) }}" 
                                        class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout> 