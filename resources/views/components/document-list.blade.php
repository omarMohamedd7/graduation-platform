@props([
    'documentableType',
    'documentableId',
    'documents' => []
])

<div class="card mb-4">
    <div class="card-header pb-0">
        <h5 class="mb-0">Documents</h5>
    </div>
    <div class="card-body">
        <!-- Document Quick Access -->
        <div class="mb-3">
            <a href="{{ route('documents.' . strtolower($documentableType) . '.index', [$documentableType === 'Project' ? 'project' : 'projectUpdate' => $documentableId]) }}" 
                class="btn bg-gradient-primary mb-0">
                <i class="fas fa-folder-open me-2"></i>Manage Documents
            </a>
            
            <a href="{{ route('documents.create', [
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId
            ]) }}" class="btn bg-gradient-dark mb-0 ms-2">
                <i class="fas fa-upload me-2"></i>Upload New
            </a>
        </div>

        <!-- Document List Preview -->
        <div class="document-list mt-4">
            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Recent Documents</h6>
            
            @if(count($documents) > 0)
                <div class="list-group">
                    @foreach($documents->take(3) as $document)
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0 text-sm">{{ $document->file_name }}</h6>
                                        @if($document->description)
                                            <p class="mb-0 text-xs text-secondary">{{ $document->description }}</p>
                                        @endif
                                        <small class="text-xs text-muted">
                                            Uploaded {{ $document->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-link btn-sm text-primary p-2">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
                
                @if(count($documents) > 3)
                    <div class="text-center mt-3">
                        <a href="{{ route('documents.' . strtolower($documentableType) . '.index', [$documentableType === 'Project' ? 'project' : 'projectUpdate' => $documentableId]) }}" 
                            class="text-primary text-sm font-weight-bold">
                            View all {{ count($documents) }} documents
                        </a>
                    </div>
                @endif
            @else
                <div class="alert alert-info text-sm" role="alert">
                    No documents have been uploaded yet.
                </div>
            @endif
        </div>
    </div>
</div> 