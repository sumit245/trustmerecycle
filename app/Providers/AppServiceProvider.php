<?php

namespace App\Providers;

use App\Models\ScrapEntry;
use App\Observers\ScrapEntryObserver;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as AppLoginResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, AppLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ScrapEntry::observe(ScrapEntryObserver::class);
    }
}
