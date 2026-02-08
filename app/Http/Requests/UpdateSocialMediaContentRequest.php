<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialMediaContentRequest extends FormRequest
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
            'social_media_category_id' => [
                'sometimes',
                'integer',
                'exists:social_media_categories,id',
            ],
            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'content' => [
                'sometimes',
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
            'social_media_category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
