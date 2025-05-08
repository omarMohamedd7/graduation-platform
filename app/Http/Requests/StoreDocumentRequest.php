<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Basic authorization is performed in the controller
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string|max:255',
            'documentable_type' => 'required|string|in:Project,ProjectUpdate',
            'documentable_id' => 'required|integer|exists:' . $this->input('documentable_type') . 's,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.required' => 'A document file is required',
            'document.file' => 'The uploaded document must be a file',
            'document.max' => 'The document may not be larger than 10MB',
            'description.max' => 'The description may not be longer than 255 characters',
            'documentable_type.required' => 'The document type is required',
            'documentable_type.in' => 'The document type must be either Project or ProjectUpdate',
            'documentable_id.required' => 'The document ID is required',
            'documentable_id.exists' => 'The selected item does not exist',
        ];
    }
} 