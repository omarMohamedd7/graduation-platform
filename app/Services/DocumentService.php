<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Store a new document
     *
     * @param UploadedFile $file The uploaded file
     * @param Model $model The model to attach the document to
     * @param string|null $description Optional description of the document
     * @return Document The created document
     */
    public function storeDocument(UploadedFile $file, Model $model, ?string $description = null): Document
    {
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getMimeType();
        $uniqueFileName = time() . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        
        // Determine storage directory based on model type
        $modelType = class_basename($model);
        $storagePath = 'documents/' . strtolower($modelType) . '/' . $model->id;
        
        // Upload and store file
        $filePath = $file->storeAs($storagePath, $uniqueFileName, 'public');
        
        // Create document record
        $document = Document::create([
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'uploaded_by' => Auth::id(),
            'description' => $description,
            'documentable_id' => $model->id,
            'documentable_type' => get_class($model),
        ]);
        
        Log::info('Document uploaded successfully', [
            'document_id' => $document->id,
            'model_type' => $modelType,
            'model_id' => $model->id,
            'user_id' => Auth::id(),
        ]);
        
        return $document;
    }
    
    /**
     * Get a document by ID
     *
     * @param int $id The document ID
     * @return Document
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDocumentById(int $id): Document
    {
        return Document::with('uploader')->findOrFail($id);
    }
    
    /**
     * Delete a document
     *
     * @param Document $document The document to delete
     * @return bool Whether the deletion was successful
     */
    public function deleteDocument(Document $document): bool
    {
        // Delete the physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        // Delete the database record
        $deleted = $document->delete();
        
        Log::info('Document deleted', [
            'document_id' => $document->id,
            'file_path' => $document->file_path,
            'deleted_by' => Auth::id(),
        ]);
        
        return $deleted;
    }
    
    /**
     * Get all documents for a model
     *
     * @param Model $model The model to get documents for
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDocumentsForModel(Model $model)
    {
        return Document::where([
            'documentable_id' => $model->id,
            'documentable_type' => get_class($model),
        ])->with('uploader')->latest()->get();
    }
} 