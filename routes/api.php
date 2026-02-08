<?php

use App\Http\Controllers\API\SocialMediaCategoryController;
use App\Http\Controllers\API\SocialMediaContentController;
use App\Http\Controllers\API\SocialMediaPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.betterbewillie')->group(function () {
    /* Category Management */
    Route::apiResource('social_media_categories', SocialMediaCategoryController::class)->except(['update']);

    /* Content Management */
    Route::get('social_media_contents/autopost', [SocialMediaContentController::class, 'autopost'])->name('social_media_contents.autopost');
    Route::apiResource('social_media_contents', SocialMediaContentController::class)->except(['update']);

    /* Post Management */
    Route::apiResource('social_media_posts', SocialMediaPostController::class)->except(['update']);
});
