<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SocialMediaContent
 */
class SocialMediaContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            /** @example 1 */
            'id' => $this->id,
            /** @example 1 */
            'account_id' => $this->account_id,
            /** @example "10 Tips for Better Social Media Engagement" */
            'title' => $this->title,
            /** @example "Boost your social media presence with these proven strategies for increasing engagement and growing your audience." */
            'content' => $this->content,
            'category' => SocialMediaCategoryResource::slim($this->whenLoaded('category')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'posts' => SocialMediaPostResource::collection($this->whenLoaded('posts')),
            'schedules' => SocialMediaScheduleResource::collection($this->whenLoaded('schedules')),
            'rewrites' => ContentGenerationRequestResource::collection($this->whenLoaded('rewrites')),
        ];

        /** @example "2026-02-09T12:00:00.000000Z" */
        $data['created_at'] = $this->created_at;
        /** @example "2026-02-09T14:30:00.000000Z" */
        $data['updated_at'] = $this->updated_at;

        return $data;
    }
}
