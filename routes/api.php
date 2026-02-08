<?php

use App\Http\Controllers\API\AIGenerateController;
use App\Http\Controllers\API\AIRewriteController;
use App\Http\Controllers\API\AIStatusController;
use App\Http\Controllers\API\SocialMediaAutopostController;
use App\Http\Controllers\API\SocialMediaCategoryController;
use App\Http\Controllers\API\SocialMediaContentController;
use App\Http\Controllers\API\SocialMediaPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.betterbewillie')->group(function () {
    /* Category Management */
    Route::apiResource('social_media_categories', SocialMediaCategoryController::class)->except(['update']);

    /* Content Management */
    Route::get('social_media_contents/autopost', SocialMediaAutopostController::class)->name('social_media_contents.autopost');
    Route::post('social_media_contents/{social_media_content}/rewrite', AIRewriteController::class)->name('social_media_contents.rewrite');
    Route::post('social_media_contents/generate', AIGenerateController::class)->name('social_media_contents.generate');
    Route::get('social_media_contents/generate/{content_generation_request}', AIStatusController::class)->name('social_media_contents.generate.status');
    Route::apiResource('social_media_contents', SocialMediaContentController::class);

    /* Post Management */
    Route::apiResource('social_media_posts', SocialMediaPostController::class)->except(['update']);
});
