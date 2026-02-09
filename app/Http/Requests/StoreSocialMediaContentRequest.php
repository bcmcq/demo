<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialMediaContentRequest extends FormRequest
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
            'social_media_category_id' => [
                'required',
                'integer',
                'exists:social_media_categories,id',
            ],
            /** @example "10 Tips for Better Social Media Engagement" */
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            /** @example "Boost your social media presence with these proven strategies." */
            'content' => [
                'required',
                'string',
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
            'social_media_category_id.required' => 'A category is required.',
            'social_media_category_id.exists' => 'The selected category does not exist.',
            'title.required' => 'A title is required.',
            'content.required' => 'Content is required.',
        ];
    }
}
