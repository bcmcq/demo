<?php

use App\Http\Controllers\API\SocialMediaCategoryController;
use App\Http\Controllers\API\SocialMediaContentController;
use App\Http\Controllers\API\SocialMediaPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.betterbewillie')->group(function () {
    Route::apiResource('social_media_contents', SocialMediaContentController::class)->except(['update']);
    Route::apiResource('social_media_categories', SocialMediaCategoryController::class)->except(['update']);
    Route::apiResource('social_media_posts', SocialMediaPostController::class)->except(['update']);
});
