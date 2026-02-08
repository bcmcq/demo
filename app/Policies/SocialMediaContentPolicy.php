<?php

namespace App\Policies;

use App\Models\SocialMediaContent;
use App\Models\User;

class SocialMediaContentPolicy
{
    /**
     * Determine whether the user can view any content.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the content.
     */
    public function view(User $user, SocialMediaContent $socialMediaContent): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }

    /**
     * Determine whether the user can create content.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(User $user, SocialMediaContent $socialMediaContent): bool
    {
        return $user->account_id === $socialMediaContent->account_id;
    }

    /**
     * Determine whether the user can use the autopost selection.
     */
    public function autopost(User $user): bool
    {
        return true;
    }
}
