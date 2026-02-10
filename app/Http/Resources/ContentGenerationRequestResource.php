<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ContentGenerationRequest
 */
class ContentGenerationRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example "9d5e8a2c-0000-4000-8000-000000000001" */
            'id' => $this->id,
            /** @example "rewrite" */
            'type' => $this->type,
            /** @example "pending" */
            'status' => $this->status->value,
            /** @example "linkedin" */
            'platform' => $this->platform->value,
            /** @example "professional" */
            'tone' => $this->tone->value,
            /** @example {"text": "Rewritten content..."} */
            'generated_content' => $this->generated_content,
            /** @example null */
            'error' => $this->error,
            /** @example "2026-02-09T12:00:00.000000Z" */
            'created_at' => $this->created_at,
            /** @example "2026-02-09T12:00:00.000000Z" */
            'updated_at' => $this->updated_at,
        ];
    }
}
