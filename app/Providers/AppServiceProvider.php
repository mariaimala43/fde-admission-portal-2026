<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\DailyAdmission;
use App\Observers\DailyAdmissionObserver;

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
        //
        DailyAdmission::observe(DailyAdmissionObserver::class);
    }
}
