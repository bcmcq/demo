<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public function scheduledSocialMediaPosts(): HasMany
    {
        return $this->hasMany(SocialMediaSchedule::class);
    }

    public function postedSocialMediaPosts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    public function socialMediaCategoryWeights(): HasMany
    {
        return $this->hasMany(SocialMediaAccountCategoryWeight::class);
    }
}
