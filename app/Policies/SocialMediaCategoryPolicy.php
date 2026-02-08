<?php

namespace App\Policies;

use App\Models\SocialMediaCategory;
use App\Models\User;

class SocialMediaCategoryPolicy
{
    /**
     * Determine whether the user can view any categories.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, SocialMediaCategory $socialMediaCategory): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, SocialMediaCategory $socialMediaCategory): bool
    {
        return $user->isAdmin();
    }
}
