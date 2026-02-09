<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SocialMediaSchedule
 */
class SocialMediaScheduleResource extends JsonResource
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
            /** @example 3 */
            'social_media_content_id' => $this->social_media_content_id,
            /** @example "2026-02-14T09:00:00.000000Z" */
            'scheduled_at' => $this->scheduled_at,
            'content' => new SocialMediaContentResource($this->whenLoaded('content')),
        ];
    }
}
