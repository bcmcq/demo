<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'website',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function socialMediaContents(): HasMany
    {
        return $this->hasMany(SocialMediaContent::class);
    }

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
