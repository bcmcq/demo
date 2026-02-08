<?php

namespace App\Policies;

use App\Models\SocialMediaPost;
use App\Models\User;

class SocialMediaPostPolicy
{
    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, SocialMediaPost $socialMediaPost): bool
    {
        return $user->account_id === $socialMediaPost->account_id;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, SocialMediaPost $socialMediaPost): bool
    {
        return $user->account_id === $socialMediaPost->account_id;
    }
}
