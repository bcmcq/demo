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
            'id' => $this->id,
            'account_id' => $this->account_id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => SocialMediaCategoryResource::slim($this->whenLoaded('category')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'posts' => SocialMediaPostResource::collection($this->whenLoaded('posts')),
            'schedules' => SocialMediaScheduleResource::collection($this->whenLoaded('schedules')),
        ];

        $data['created_at'] = $this->created_at;
        $data['updated_at'] = $this->updated_at;

        return $data;
    }
}
