<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\SocialMediaContent;
use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any media for the content.
     */
    public function viewAny(User $user, SocialMediaContent $socialMediaContent): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }

    /**
     * Determine whether the user can view a specific media item.
     */
    public function view(User $user, SocialMediaContent $socialMediaContent, Media $media): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }

    /**
     * Determine whether the user can create media for the content.
     */
    public function create(User $user, SocialMediaContent $socialMediaContent): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, SocialMediaContent $socialMediaContent, Media $media): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }
}
