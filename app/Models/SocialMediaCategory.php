<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(SocialMediaContent::class);
    }

    public function accountWeights(): HasMany
    {
        return $this->hasMany(SocialMediaAccountCategoryWeight::class);
    }
}
