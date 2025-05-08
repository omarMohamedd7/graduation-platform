@props([
    'documentableType',
    'documentableId',
    'documents' => [],
])

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Documents</h5>
    </div>
    <div class="card-body">
        <!-- Document Upload Form -->
        <form id="documentUploadForm" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="document" class="form-label">Upload new document</label>
                <input class="form-control" type="file" id="document" name="document" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description (optional)</label>
                <input type="text" class="form-control" id="description" name="description" maxlength="255">
            </div>
            <button type="submit" class="btn bg-gradient-dark">Upload Document</button>
        </form>

        <hr>

        <!-- Document List -->
        <div class="document-list">
            <h6>Uploaded Documents</h6>
            <div id="documents-container">
                @if(count($documents) > 0)
                    @foreach($documents as $document)
                        <div class="document-item d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>{{ $document->file_name }}</strong>
                                @if($document->description)
                                    <p class="mb-0 text-sm text-muted">{{ $document->description }}</p>
                                @endif
                                <p class="mb-0 text-xs">
                                    Uploaded by {{ $document->uploader->full_name }} on {{ $document->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-document" data-document-id="{{ $document->id }}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No documents uploaded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('documentUploadForm');
    const documentsContainer = document.getElementById('documents-container');
    const apiEndpoint = '{{ $documentableType === "Project" 
        ? "/api/projects/{$documentableId}/documents" 
        : "/api/project-updates/{$documentableId}/documents" }}';

    // Handle document upload
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(uploadForm);
        
        fetch(apiEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Document uploaded successfully');
                
                // Reset form
                uploadForm.reset();
                
                // Refresh document list (alternatively, you could add the new document to the list without reloading)
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to upload document'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during upload');
        });
    });
    
    // Handle document deletion
    document.querySelectorAll('.delete-document').forEach(button => {
        button.addEventListener('click', function() {
            const documentId = this.getAttribute('data-document-id');
            if (confirm('Are you sure you want to delete this document?')) {
                fetch(`/api/documents/${documentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the document item from the DOM
                        this.closest('.document-item').remove();
                        
                        // Show success message
                        alert('Document deleted successfully');
                        
                        // If all documents are deleted, show "no documents" message
                        if (documentsContainer.querySelectorAll('.document-item').length === 0) {
                            documentsContainer.innerHTML = '<p class="text-muted">No documents uploaded yet.</p>';
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete document'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during deletion');
                });
            }
        });
    });
});
</script>
@endpush 