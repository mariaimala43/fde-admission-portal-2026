<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\UnionCouncilController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Hoi\ProfileController;
use App\Http\Controllers\Hoi\ClassSetupController;
use App\Http\Controllers\Hoi\EnrollmentController;
 use App\Http\Controllers\Hoi\DailyAdmissionController;
 use App\Http\Controllers\Hoi\AdmissionReportController;
use App\Http\Controllers\Fde\DashboardController as FdeDashboardController;
use App\Http\Controllers\Fde\SchoolsReportController;
use App\Http\Controllers\Fde\MasterReportController;
use App\Http\Controllers\Aeo\DashboardController as AeoDashboardController;
use App\Http\Controllers\Public\PortalController;




// ── Public routes ──────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ── Protected routes ───────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/me',        [AuthController::class, 'me'])->name('me');

    // ── HoI Profile Setup ──────────────────────────────────
    Route::middleware(['role:hoi'])->prefix('hoi')->name('hoi.')->group(function () {
        Route::get('setup',  [ProfileController::class, 'setup'])->name('profile.setup');
        Route::post('setup', [ProfileController::class, 'saveSetup'])->name('profile.save');


        Route::get('classes',  [ClassSetupController::class, 'index'])->name('classes.setup');
        Route::post('classes', [ClassSetupController::class, 'save'])->name('classes.save');


        Route::get('enrollment',  [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('enrollment', [EnrollmentController::class, 'save'])->name('enrollment.save');



        Route::get('admissions/daily',  [DailyAdmissionController::class, 'index'])->name('admissions.daily');
        Route::post('admissions/daily', [DailyAdmissionController::class, 'save'])->name('admissions.save');



        Route::get('admissions/report', [AdmissionReportController::class, 'index'])->name('admissions.report');

    });


Route::middleware(['auth', 'role:fde_cell'])
    ->prefix('fde')
    ->name('fde.')
    ->group(function () {
        Route::get('dashboard',         [FdeDashboardController::class,  'index'])->name('dashboard');
        Route::get('schools',           [SchoolsReportController::class, 'index'])->name('schools.index');
        Route::get('schools/{institution}', [SchoolsReportController::class, 'show'])->name('schools.show');
        Route::get('reports/master', [MasterReportController::class, 'index'])->name('reports.master');
    });

// ── AEO routes ────────────────────────────────────────
Route::middleware(['auth', 'role:aeo'])
    ->prefix('aeo')
    ->name('aeo.')
    ->group(function () {
        Route::get('dashboard', [AeoDashboardController::class, 'index'])->name('dashboard');
    });

// ── AJAX routes (any auth) ─────────────────────────────
Route::get('ajax/sectors',      [ProfileController::class, 'getSectors'])->name('ajax.sectors');
Route::get('ajax/institutions', [ProfileController::class, 'getInstitutions'])->name('ajax.institutions');



Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',                      [PortalController::class, 'index'])->name('index');
    Route::get('school/{institution}',   [PortalController::class, 'show'])->name('show');
});
    // ── Admin only routes (fde_cell role) ──────────────────
    Route::middleware(['role:fde_cell'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        // Union Councils
        Route::resource('ucs', UnionCouncilController::class)
            ->except(['destroy', 'show'])
            ->parameters(['ucs' => 'unionCouncil']);

        // Sectors
        Route::resource('sectors', SectorController::class)
            ->except(['destroy', 'show']);

        // Institutions
        Route::resource('institutions', InstitutionController::class)
            ->except(['destroy']);

        // Users
        Route::resource('users', UserController::class)
            ->except(['destroy', 'show']);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

            });
            // Import
        Route::get('import',  [ImportController::class, 'index'])->name('admin.import.index');
        Route::post('import', [ImportController::class, 'store'])->name('admin.import.store');

});
