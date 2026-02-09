<?php

namespace App\Http\Requests;

use App\Enums\MimeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresignedUrlRequest extends FormRequest
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
            'file_name' => [
                'required',
                'string',
                'max:255',
            ],
            'content_type' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(MimeType::videoValues()),
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
            'file_name.required' => 'A file name is required for the presigned URL.',
            'file_name.max' => 'The file name must not exceed 255 characters.',
            'content_type.in' => 'The content type must be one of: '.implode(', ', MimeType::videoValues()).'.',
        ];
    }
}
