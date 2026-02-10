<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $apiPath = config('scramble.api_path', 'api');

        Scramble::configure()
            ->expose(
                ui: '/api/docs',
                document: '/api/docs.json',
            )
            ->routes(function (Route $route) use ($apiPath) {
                $domain = config('scramble.api_domain');
                $isBaseMatching = ! $apiPath || Str::startsWith($route->uri(), $apiPath);
                if (! $isBaseMatching || ($domain && $route->getDomain() !== $domain)) {
                    return false;
                }
                if (in_array($route->uri(), ["{$apiPath}/docs", "{$apiPath}/docs.json"], true)) {
                    return false;
                }

                return true;
            })
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }
}
