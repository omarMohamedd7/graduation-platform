<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Upload Document"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">
                                    Upload New Document
                                </h6>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="p-4">
                                <h5 class="mb-4">
                                    @if($documentableType === 'Project')
                                        Upload Document for Project: {{ $model->title }}
                                    @else
                                        Upload Document for Project Update: {{ $model->title }}
                                    @endif
                                </h5>

                                @if(session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
                                    <input type="hidden" name="documentable_id" value="{{ $documentableId }}">

                                    <div class="mb-3">
                                        <label for="document" class="form-label">Document File</label>
                                        <input type="file" class="form-control @error('document') is-invalid @enderror" 
                                            id="document" name="document" required>
                                        @error('document')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Accepted file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR, JPG, PNG, GIF
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description (optional)</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                            id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <a href="{{ route($returnRoute, $returnRouteParams) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn bg-gradient-primary">
                                            <i class="fas fa-upload me-2"></i> Upload Document
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout> 