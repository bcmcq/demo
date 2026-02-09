<?php

namespace App\Http\Requests;

use App\Enums\Platform;
use App\Enums\Tone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RewriteContentRequest extends FormRequest
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
            /** @example "twitter" */
            'platform' => [
                'required',
                Rule::enum(Platform::class),
            ],
            /** @example "professional" */
            'tone' => [
                'required',
                Rule::enum(Tone::class),
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
            'platform.required' => 'A platform is required.',
            'platform.in' => 'The platform must be one of: twitter, instagram, facebook, linkedin.',
            'tone.required' => 'A tone is required.',
            'tone.in' => 'The tone must be one of: professional, casual, humorous, formal.',
        ];
    }
}
