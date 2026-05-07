<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Helpers\InstitutionHelper;
use App\Models\Admission;
use App\Models\DailyAdmission;
use App\Models\StaffStrengthRegister;
use App\Observers\AdmissionObserver;
use App\Observers\DailyAdmissionObserver;
use App\Policies\StaffStrengthPolicy;

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
        DailyAdmission::observe(DailyAdmissionObserver::class);
        Admission::observe(AdmissionObserver::class);

        Gate::policy(StaffStrengthRegister::class, StaffStrengthPolicy::class);
        Route::model('staffStrength', StaffStrengthRegister::class);

        // @hasFeature('matric_tech') ... @endhasFeature
        Blade::if('hasFeature', fn(string $feature) => InstitutionHelper::hasFeature($feature));
    }
}
