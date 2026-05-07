<?php

use App\Http\Controllers\Api\V1\AdmissionController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\SchoolVacancyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::get('schools/vacancies', [SchoolVacancyController::class, 'index']);

    Route::post('admissions', [AdmissionController::class, 'store']);
    Route::get('admissions/{ref_id}', [AdmissionController::class, 'show']);
    Route::put('admissions/{ref_id}/status', [AdmissionController::class, 'updateStatus']);

    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('dashboard/schools', [DashboardController::class, 'schools']);
});
