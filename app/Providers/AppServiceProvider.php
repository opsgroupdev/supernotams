<?php

namespace App\Providers;

use App\Actions\NotamICAOFetcher;
use App\Actions\NotamOpenAiTagger;
use App\Contracts\NotamFetcher;
use App\Contracts\NotamTagger;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(NotamFetcher::class, NotamICAOFetcher::class);
        $this->app->bind(NotamTagger::class, NotamOpenAiTagger::class);
    }
}
