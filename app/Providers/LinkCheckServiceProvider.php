<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LinkCheckService;

class LinkCheckServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LinkCheckService::class, function ($app) {
            // Only instantiate if we're not running migrations
            if (!$this->app->runningInConsole() || !str_contains(request()->server('argv')[1] ?? '', 'migrate')) {
                return new LinkCheckService();
            }
            return null;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
