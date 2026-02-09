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
            'id' => $this->id,
            'account_id' => $this->account_id,
            'social_media_content_id' => $this->social_media_content_id,
            'scheduled_at' => $this->scheduled_at,
            'content' => new SocialMediaContentResource($this->whenLoaded('content')),
        ];
    }
}
