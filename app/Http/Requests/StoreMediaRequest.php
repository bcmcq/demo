<?php

namespace App\Http\Requests;

use App\Enums\MimeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required_without:storage_key',
                'file',
                File::types(['jpeg', 'jpg', 'png', 'gif', 'webp'])->max(2 * 1024),
            ],
            /** @example "videos/abc123_promo.mp4" */
            'storage_key' => [
                'required_without:file',
                'string',
                'max:500',
            ],
            /** @example "promo.mp4" */
            'file_name' => [
                'required_with:storage_key',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required_without' => 'An image file is required when no storage key is provided.',
            'file.max' => 'The image must not exceed 2MB.',
            'storage_key.required_without' => 'A storage key is required when no file is uploaded.',
            'file_name.required_with' => 'A file name is required when providing a storage key.',
        ];
    }
}
