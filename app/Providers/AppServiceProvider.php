<?php

namespace App\Providers;

use App\Actions\NotamICAOFetcher;
use App\Actions\NotamOpenAiTagger;
use App\Contracts\NotamFetcher;
use App\Contracts\NotamTagger;
use App\Models\User;
use Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        NotamFetcher::class => NotamICAOFetcher::class,
        NotamTagger::class  => NotamOpenAiTagger::class,
    ];

    public array $singletons = [
    ];

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
        Gate::define('viewPulse', function (User $user) {
            return in_array($user->email, [
                config('horizon.admin_email'),
            ]);
        });
    }
}
