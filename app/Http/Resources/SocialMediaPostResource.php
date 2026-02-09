<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SocialMediaPost
 */
class SocialMediaPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 1 */
            'id' => $this->id,
            /** @example 1 */
            'account_id' => $this->account_id,
            /** @example 5 */
            'social_media_content_id' => $this->social_media_content_id,
            /** @example "2026-02-09T10:00:00.000000Z" */
            'posted_at' => $this->posted_at,
            'content' => new SocialMediaContentResource($this->whenLoaded('content')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
