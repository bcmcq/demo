<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialMediaPostRequest extends FormRequest
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
            /** @example 1 */
            'social_media_content_id' => [
                'required',
                'integer',
                'exists:social_media_contents,id',
            ],
            /** @example "2026-02-09T10:00:00Z" */
            'posted_at' => [
                'nullable',
                'date',
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
            'social_media_content_id.required' => 'A content reference is required.',
            'social_media_content_id.exists' => 'The selected content does not exist.',
            'posted_at.date' => 'The posted at field must be a valid date.',
        ];
    }
}
