<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Translation\TranslationInterface;
use App\Services\Translation\TranslationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind( TranslationInterface::class,TranslationService::class );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
