<?php

use App\Http\Controllers\API\AIContentController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\SocialMediaAutopostController;
use App\Http\Controllers\API\SocialMediaCategoryController;
use App\Http\Controllers\API\SocialMediaContentController;
use App\Http\Controllers\API\SocialMediaPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.betterbewillie')->group(function () {
    /* Category Management */
    Route::apiResource('social_media_categories', SocialMediaCategoryController::class)->except(['update']);

    /* Content Management */
    Route::prefix('social_media_contents')->name('social_media_contents.')->group(function () {
        Route::get('autopost', SocialMediaAutopostController::class)->name('autopost');
        Route::post('{social_media_content}/rewrite', [AIContentController::class, 'rewrite'])->name('rewrite');
        Route::post('generate', [AIContentController::class, 'generate'])->name('generate');
        Route::get('generate/{content_generation_request}', [AIContentController::class, 'status'])->name('generate.status');
    });
    Route::apiResource('social_media_contents', SocialMediaContentController::class);

    /* Media Management (nested under content) */
    Route::post('social_media_contents/{social_media_content}/media/presigned_url', [MediaController::class, 'presignedUrl'])
        ->name('social_media_contents.media.presigned-url');
    Route::apiResource('social_media_contents.media', MediaController::class)
        ->except(['update'])
        ->parameters(['media' => 'media']);

    /* Post Management */
    Route::apiResource('social_media_posts', SocialMediaPostController::class)->except(['update']);
});
