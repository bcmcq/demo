<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Media
 */
class MediaResource extends JsonResource
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
            /** @example 5 */
            'social_media_content_id' => $this->social_media_content_id,
            /** @example "image" */
            'type' => $this->type,
            /** @example "campaign-hero.jpg" */
            'file_name' => $this->file_name,
            /** @example "image/jpeg" */
            'mime_type' => $this->mime_type,
            /** @example 1048576 */
            'size' => $this->size,
            /** @example "https://storage.example.com/images/abc123_campaign-hero.jpg" */
            'url' => $this->url,
            /** @example "https://storage.example.com/thumbnails/abc123_campaign-hero.jpg" */
            'thumbnail_url' => $this->thumbnail_url,
            /** @example "https://stream.mux.com/abc123.m3u8" */
            'playback_url' => $this->when($this->isVideo(), $this->playback_url),
            /** @example "2026-02-09T12:00:00.000000Z" */
            'created_at' => $this->created_at,
            /** @example "2026-02-09T12:00:00.000000Z" */
            'updated_at' => $this->updated_at,
        ];
    }
}
