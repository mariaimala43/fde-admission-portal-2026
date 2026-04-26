<?php
// SAVE AS: routes/web.php — FULL REPLACEMENT

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\UnionCouncilController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Hoi\ProfileController;
use App\Http\Controllers\Hoi\ClassSetupController;
use App\Http\Controllers\Hoi\EnrollmentController;
use App\Http\Controllers\Hoi\DailyAdmissionController;
use App\Http\Controllers\Hoi\AdmissionReportController;
use App\Http\Controllers\Hoi\AdmissionCorrectionController as HoiCorrectionController;
use App\Http\Controllers\Hoi\StudentTransferController as HoiTransferController;
use App\Http\Controllers\Hoi\ReferralController as HoiReferralController;
use App\Http\Controllers\Hoi\AdmissionMonitoringController as HoiMonitoringController;
use App\Http\Controllers\Hoi\FacilityController;
use App\Http\Controllers\Hoi\AdmissionQuotaController;
use App\Http\Controllers\Hoi\NotificationController as HoiNotificationController;
use App\Http\Controllers\Hoi\MeritListController as HoiMeritListController;
use App\Http\Controllers\Fde\DashboardController as FdeDashboardController;
use App\Http\Controllers\Fde\SchoolsReportController;
use App\Http\Controllers\Fde\MasterReportController;
use App\Http\Controllers\Fde\ReportDashboardController;
use App\Http\Controllers\Fde\ExportController;
use App\Http\Controllers\Fde\StudentTransferController as FdeTransferController;
use App\Http\Controllers\Fde\ReferralController as FdeReferralController;
use App\Http\Controllers\Fde\AdmissionMonitoringController as FdeMonitoringController;
use App\Http\Controllers\Fde\EnrollmentOverrideController;
use App\Http\Controllers\Fde\AdmissionOverrideController;
use App\Http\Controllers\Fde\AdmissionCorrectionController as FdeCorrectionController;
use App\Http\Controllers\Fde\AdmissionEditGrantController;
use App\Http\Controllers\Fde\AuditLogController;
use App\Http\Controllers\Fde\PortalSettingsController;
use App\Http\Controllers\Fde\SeatConfigurationController;
use App\Http\Controllers\Fde\AdmissionPeriodController;
use App\Http\Controllers\Aeo\DashboardController as AeoDashboardController;
use App\Http\Controllers\Aeo\MonitoringController as AeoMonitoringController;
use App\Http\Controllers\Director\MonitoringController as DirectorMonitoringController;
use App\Http\Controllers\Director\DashboardController as DirectorDashboardController;
use App\Http\Controllers\Public\PortalController;
use App\Http\Controllers\Hoi\RoomAllocationController as HoiRoomController;
use App\Http\Controllers\Fde\RoomAllocationController as FdeRoomController;
use App\Http\Controllers\Fde\AiAgentDataController;
use App\Http\Controllers\Fde\AppSettingsController;
use App\Http\Controllers\Fde\SystemResetController;
use App\Http\Controllers\Fde\UcControlRoomController;
use App\Http\Controllers\Fde\CollegeController;
use App\Http\Controllers\Fde\MeritListController as FdeMeritListController;
use App\Http\Controllers\Fde\AnnouncementController;
use App\Http\Controllers\Hoi\StaffStrengthController as HoiStaffStrengthController;
use App\Http\Controllers\Fde\StaffStrengthController as FdeStaffStrengthController;
use App\Http\Controllers\Aeo\StaffStrengthController as AeoStaffStrengthController;
use App\Http\Controllers\Director\StaffStrengthController as DirectorStaffStrengthController;


// ══════════════════════════════════════════════════════════════════════════
//  PUBLIC ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::get('/', fn() => redirect()->route('login'));

Route::get( '/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']    )->name('login.post')->middleware('throttle:5,1');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/',                    [PortalController::class, 'index']     )->name('index');
    Route::get('merit-lists',          [PortalController::class, 'meritLists'])->name('merit-lists');
    Route::get('school/{institution}', [PortalController::class, 'show']      )->name('show');
    Route::get('seats',                [PortalController::class, 'seats']     )->name('seats');
});

// Merit list file download — served through Laravel (works on any hosting)
Route::get('/merit-file/{meritList}', function (\App\Models\InstitutionMeritList $meritList) {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    if (! $disk->exists($meritList->file_path)) {
        abort(404, 'File not found.');
    }
    return $disk->response($meritList->file_path, $meritList->original_name);
})->name('merit.file');

Route::get( '/forgot-password', [ForgotPasswordController::class, 'showForm']     )->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get( '/reset-password',  [ResetPasswordController::class,  'showForm']     )->name('password.reset');
Route::post('/reset-password',  [ResetPasswordController::class,  'reset']        )->name('password.update');


// ══════════════════════════════════════════════════════════════════════════
//  AUTHENTICATED ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth'])->group(function () {

    Route::post('/logout',    [AuthController::class, 'logout']   )->name('logout');
    Route::get( '/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get( '/me',        [AuthController::class, 'me']       )->name('me');

    Route::get('ajax/sectors',      [ProfileController::class, 'getSectors']     )->name('ajax.sectors');
    Route::get('ajax/institutions', [ProfileController::class, 'getInstitutions'])->name('ajax.institutions');


    // ════════════════════════════════════════════════════════════════════════
    //  HOI — Head of Institution
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:hoi'])->prefix('hoi')->name('hoi.')->group(function () {

        // Profile
        Route::get( 'setup', [ProfileController::class, 'setup']    )->name('profile.setup');
        Route::post('setup', [ProfileController::class, 'saveSetup'])->name('profile.save');

        // Classes & sections
        Route::get( 'classes', [ClassSetupController::class, 'index'])->name('classes.setup')->middleware('can:section.manage');
        Route::post('classes', [ClassSetupController::class, 'save'] )->name('classes.save')->middleware('can:section.manage');

        // Facility settings
        Route::get( 'facilities', [FacilityController::class, 'index'])->name('facilities.index');
        Route::post('facilities', [FacilityController::class, 'save'] )->name('facilities.save');

        // Baseline enrollment
        Route::get( 'enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('enrollment', [EnrollmentController::class, 'save'] )->name('enrollment.save');

        // Admission quota (HOI-set capacity per class)
        Route::get( 'admission-quota', [AdmissionQuotaController::class, 'index'])->name('quota.index');
        Route::post('admission-quota', [AdmissionQuotaController::class, 'save'] )->name('quota.save');

        // Daily admissions
        Route::get( 'admissions/daily', [DailyAdmissionController::class, 'index'])->name('admissions.daily');
        Route::post('admissions/daily', [DailyAdmissionController::class, 'save'] )->name('admissions.save');

        // Admission report
        Route::get('admissions/report',       [AdmissionReportController::class, 'index']       )->name('admissions.report');
        Route::get('admissions/report/excel', [AdmissionReportController::class, 'exportExcel'] )->name('admissions.report.excel');
        Route::get('admissions/report/pdf',   [AdmissionReportController::class, 'exportPdf']   )->name('admissions.report.pdf');

        // Vacancy position report
        Route::get('reports/vacancy',    [AdmissionReportController::class, 'vacancy'] )->name('reports.vacancy');

        // Correction requests — FIXED: was prefix('hoi/corrections') which caused double /hoi/hoi/
        Route::prefix('corrections')->name('corrections.')->group(function () {
            Route::get('/',       [HoiCorrectionController::class, 'index'] )->name('index');
            Route::get('/create', [HoiCorrectionController::class, 'create'])->name('create');
            Route::post('/',      [HoiCorrectionController::class, 'store'] )->name('store');
        });

        // Student transfers
        Route::get( 'transfers',                   [HoiTransferController::class, 'index']      )->name('transfers.index');
        Route::get( 'transfers/create',            [HoiTransferController::class, 'create']     )->name('transfers.create');
        Route::post('transfers',                   [HoiTransferController::class, 'store']      )->name('transfers.store');
        Route::get( 'transfers/{transfer}',        [HoiTransferController::class, 'show']       )->name('transfers.show');
        Route::post('transfers/{transfer}/accept', [HoiTransferController::class, 'accept']     )->name('transfers.accept');
        Route::post('transfers/{transfer}/reject', [HoiTransferController::class, 'reject']     )->name('transfers.reject');
        Route::post('transfers/{transfer}/cancel', [HoiTransferController::class, 'cancel']     )->name('transfers.cancel');
        Route::post('transfers/{transfer}/info',   [HoiTransferController::class, 'requestInfo'])->name('transfers.request-info');

        // Referrals
        Route::get(   'referrals',                                [HoiReferralController::class, 'index']          )->name('referrals.index');
        Route::get(   'referrals/{referral}',                     [HoiReferralController::class, 'show']           )->name('referrals.show');
        Route::patch( 'referrals/{referral}/accept',              [HoiReferralController::class, 'accept']         )->name('referrals.accept');
        Route::patch( 'referrals/{referral}/reject',              [HoiReferralController::class, 'reject']         )->name('referrals.reject');
        Route::post(  'referrals/{referral}/update-test',         [HoiReferralController::class, 'updateTest']     )->name('referrals.update-test');
        Route::post(  'referrals/{referral}/update-admission',    [HoiReferralController::class, 'updateAdmission'])->name('referrals.update-admission');

        // Monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get(  '/',                     [HoiMonitoringController::class, 'index']           )->name('index');
            Route::get(  '/{monitoring}',         [HoiMonitoringController::class, 'show']            )->name('show');
            Route::patch('/{monitoring}/test', [HoiMonitoringController::class, 'updateTestStatus'])->name('test');
            Route::patch('/{monitoring}/doc',  [HoiMonitoringController::class, 'updateDocStatus'] )->name('doc');
        });
       Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get(    '/',             [HoiRoomController::class, 'index']  )->name('index');
            Route::post(   '/',             [HoiRoomController::class, 'store']  )->name('store');
            Route::put(    '/{allocation}', [HoiRoomController::class, 'update'] )->name('update');
            Route::delete( '/{allocation}', [HoiRoomController::class, 'destroy'])->name('destroy');
            Route::get( '/export',        [HoiRoomController::class, 'exportPdf'])->name('export');
        });

        // In-app notifications (daily admission reminders)
        Route::get(    'notifications',              [HoiNotificationController::class, 'index']      )->name('notifications.index');
        Route::post(   'notifications/mark-all-read',[HoiNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post(   'notifications/{id}/read',    [HoiNotificationController::class, 'markRead']   )->name('notifications.mark-read');
        Route::delete( 'notifications/{id}',         [HoiNotificationController::class, 'destroy']    )->name('notifications.destroy');

        // Merit list file uploads (per school)
        Route::resource('merit-lists', HoiMeritListController::class)->only(['index', 'store', 'destroy']);

        // Staff Strength Register
        Route::prefix('staff-strength')->name('staff-strength.')->group(function () {
            Route::get( '/',     [HoiStaffStrengthController::class, 'index'])->name('index');
            Route::post('/save', [HoiStaffStrengthController::class, 'save'] )->name('save');
        });

    });


    // ════════════════════════════════════════════════════════════════════════
    //  FDE CELL
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:fde_cell'])->prefix('fde')->name('fde.')->group(function () {

        Route::get('dashboard',             [FdeDashboardController::class,  'index'])->name('dashboard');
        Route::get( 'schools',                              [SchoolsReportController::class, 'index']          )->name('schools.index');
        Route::get( 'schools/{institution}',               [SchoolsReportController::class, 'show']           )->name('schools.show');
        Route::post('schools/{institution}/reset-admissions', [SchoolsReportController::class, 'resetAdmissions'])->name('schools.reset-admissions');
        Route::post('schools/{institution}/quota',            [SchoolsReportController::class, 'saveQuota']       )->name('schools.save-quota');

        Route::get('reports/master',        [MasterReportController::class,  'index'])->name('reports.master');

        // Analytics
        Route::get('reports/dashboard', [ReportDashboardController::class, 'index'])        ->name('reports.dashboard');
        Route::get('reports/sector',    [ReportDashboardController::class, 'sectorReport']) ->name('reports.sector');
        Route::get('reports/vacancy',   [ReportDashboardController::class, 'vacancyReport'])->name('reports.vacancy');
        Route::get('reports/gender',    [ReportDashboardController::class, 'genderReport']) ->name('reports.gender');
        Route::get('reports/oosc',      [ReportDashboardController::class, 'ooscReport'])   ->name('reports.oosc');

        // Exports
        Route::get('export/master', [MasterReportController::class, 'export'])->name('export.master');
        Route::get('export/vacancy', [ExportController::class, 'vacancyReport'])->name('export.vacancy');
        Route::get('export/oosc',    [ExportController::class, 'ooscReport'])   ->name('export.oosc');

        // Student transfers
        Route::get( 'transfers',                   [FdeTransferController::class, 'index'] )->name('transfers.index');
        Route::get( 'transfers/create',            [FdeTransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers',                   [FdeTransferController::class, 'store'] )->name('transfers.store');
        Route::get( 'transfers/{transfer}',        [FdeTransferController::class, 'show']  )->name('transfers.show');
        Route::post('transfers/{transfer}/accept', [FdeTransferController::class, 'accept'])->name('transfers.accept');
        Route::post('transfers/{transfer}/reject', [FdeTransferController::class, 'reject'])->name('transfers.reject');
        Route::post('transfers/{transfer}/cancel',       [FdeTransferController::class, 'cancel']            )->name('transfers.cancel');
        Route::post('transfers/{transfer}/cross-sector', [FdeTransferController::class, 'approveCrossSector'])->name('transfers.cross-sector');

        // Referrals
        Route::get(  'referrals',                   [FdeReferralController::class, 'index'] )->name('referrals.index');
        Route::get(  'referrals/create',            [FdeReferralController::class, 'create'])->name('referrals.create');
        Route::post( 'referrals',                   [FdeReferralController::class, 'store'] )->name('referrals.store');
        Route::get(  'referrals/{referral}',        [FdeReferralController::class, 'show']  )->name('referrals.show');
        Route::get(  'referrals/{referral}/edit',   [FdeReferralController::class, 'edit']  )->name('referrals.edit');
        Route::put(  'referrals/{referral}',        [FdeReferralController::class, 'update'])->name('referrals.update');
        Route::patch('referrals/{referral}/cancel',   [FdeReferralController::class, 'cancel']  )->name('referrals.cancel');
        Route::get(  'referrals/{referral}/re-refer',  [FdeReferralController::class, 'create']  )->name('referrals.re-refer');

        // Enrollment override
        Route::get( 'enrollment/{institution}',        [EnrollmentOverrideController::class, 'show']  )->name('enrollment.show');
        Route::post('enrollment/{institution}/unlock', [EnrollmentOverrideController::class, 'unlock'])->name('enrollment.unlock');
        Route::put( 'enrollment/{institution}',        [EnrollmentOverrideController::class, 'update'])->name('enrollment.update');

        // Admissions override
        Route::get( 'admissions',                      [AdmissionOverrideController::class, 'index']   )->name('admissions.index');
        Route::post('admissions/{admission}/override', [AdmissionOverrideController::class, 'override'])->name('admissions.override');
        Route::post('admissions/{admission}/return',   [AdmissionOverrideController::class, 'return']  )->name('admissions.return');

        // Monitoring
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get( '/dashboard',          [FdeMonitoringController::class, 'dashboard']        )->name('dashboard');
            Route::get( '/',                   [FdeMonitoringController::class, 'index']            )->name('index');
            Route::get( '/{monitoring}',       [FdeMonitoringController::class, 'show']             )->name('show');
            Route::patch('/{monitoring}/test', [FdeMonitoringController::class, 'updateTestStatus'] )->name('test');
            Route::patch('/{monitoring}/merit',[FdeMonitoringController::class, 'updateMeritStatus'])->name('merit');
            Route::patch('/{monitoring}/doc',  [FdeMonitoringController::class, 'overrideDocStatus'])->name('doc');
            Route::post( '/sync',              [FdeMonitoringController::class, 'sync']             )->name('sync');
        });

        // Rooms
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get( '/',         [FdeRoomController::class, 'index']    )->name('index');
            Route::get( '/export',   [FdeRoomController::class, 'exportPdf'])->name('export');
            Route::get( '/{room}',   [FdeRoomController::class, 'show']     )->name('show');
        });

        // Correction requests
        Route::prefix('corrections')->name('corrections.')->group(function () {
            Route::get( '/',                      [FdeCorrectionController::class, 'index']  )->name('index');
            Route::get( '/{correction}',          [FdeCorrectionController::class, 'show']   )->name('show');
            Route::post('/{correction}/approve',  [FdeCorrectionController::class, 'approve'])->name('approve');
            Route::post('/{correction}/reject',   [FdeCorrectionController::class, 'reject'] )->name('reject');
        });

        // Edit Grants — Option B (FDE grants HOI temporary post-lock edit permission)
        Route::prefix('admission-grants')->name('admission-grants.')->group(function () {
            Route::get( '/',               [AdmissionEditGrantController::class, 'index'] )->name('index');
            Route::get( '/create',         [AdmissionEditGrantController::class, 'create'])->name('create');
            Route::post('/',               [AdmissionEditGrantController::class, 'store'] )->name('store');
            Route::post('/{grant}/revoke', [AdmissionEditGrantController::class, 'revoke'])->name('revoke');
        });

        // Audit log
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/',          [AuditLogController::class, 'index'] )->name('index');
            Route::get('/export',    [AuditLogController::class, 'export'])->name('export');
            Route::get('/{auditLog}',[AuditLogController::class, 'show']  )->name('show');
        });

        // Admission period management
        Route::prefix('admission-period')->name('admission-period.')->middleware('can:admission_period.manage')->group(function () {
            Route::get('/',                          [AdmissionPeriodController::class, 'index']          )->name('index');
            Route::put('/{academicYear}',            [AdmissionPeriodController::class, 'update']         )->name('update');
            Route::post('/open',                     [AdmissionPeriodController::class, 'openAdmissions'] )->name('open');
            Route::post('/close',                    [AdmissionPeriodController::class, 'closeAdmissions'])->name('close');
        });

        // Seat configuration
        Route::prefix('seats')->name('seats.')->middleware('can:seats.configure')->group(function () {
            Route::get( '/',                    [SeatConfigurationController::class, 'index'] )->name('index');
            Route::get( '/{institution}',       [SeatConfigurationController::class, 'edit']  )->name('edit');
            Route::put( '/{institution}',       [SeatConfigurationController::class, 'update'])->name('update');
            Route::post('/{institution}/lock',  [SeatConfigurationController::class, 'lock']        )->name('lock');
            Route::post('/{institution}/unlock',[SeatConfigurationController::class, 'unlock']      )->name('unlock');
            Route::post('/{institution}/sync',  [SeatConfigurationController::class, 'syncClasses'] )->name('sync');
        });

        // Portal settings
        Route::prefix('portal-settings')->name('portal-settings.')->group(function () {
            Route::get( '/', [PortalSettingsController::class, 'index'] )->name('index');
            Route::put( '/', [PortalSettingsController::class, 'update'])->name('update');
        });

        // Merit list management (FDE can upload for any institution)
        Route::prefix('merit-lists')->name('merit-lists.')->group(function () {
            Route::get( '/',               [FdeMeritListController::class, 'index']  )->name('index');
            Route::post('/',               [FdeMeritListController::class, 'store']  )->name('store');
            Route::delete('/{meritList}',  [FdeMeritListController::class, 'destroy'])->name('destroy');
        });
        // Theme Customizer
        Route::get( 'theme',       [\App\Http\Controllers\Fde\ThemeController::class, 'index']  )->name('theme.index');
        Route::put( 'theme',       [\App\Http\Controllers\Fde\ThemeController::class, 'update'] )->name('theme.update');
        Route::post('theme/reset', [\App\Http\Controllers\Fde\ThemeController::class, 'reset']  )->name('theme.reset');

            // AI Report Studio page
        Route::get('ai-reports', [AiAgentDataController::class, 'studio'])->name('ai.reports');
        Route::post('api/ai-insights', [AiAgentDataController::class,'insights']);

        // Data + Claude API routes (also inside same group, or nested prefix)
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('agent-data',   [AiAgentDataController::class, 'agentData'])->name('agent-data');
            Route::post('ai-generate', [AiAgentDataController::class, 'generate'] )->name('ai-generate');
        });

        // Staff Strength Register
        Route::prefix('staff-strength')->name('staff-strength.')->group(function () {
            Route::get( '/',                              [FdeStaffStrengthController::class, 'index']      )->name('index');
            Route::get( '/{staffStrength}',               [FdeStaffStrengthController::class, 'show']       )->name('show');
            Route::get( '/{staffStrength}/edit',          [FdeStaffStrengthController::class, 'edit']       )->name('edit');
            Route::put( '/{staffStrength}',               [FdeStaffStrengthController::class, 'update']     )->name('update');
            Route::post('/{staffStrength}/lock',          [FdeStaffStrengthController::class, 'lock']       )->name('lock');
            Route::post('/{staffStrength}/unlock',        [FdeStaffStrengthController::class, 'unlock']     )->name('unlock');
            Route::get( '/{staffStrength}/export-pdf',    [FdeStaffStrengthController::class, 'exportPdf']  )->name('export-pdf');
            Route::get( '/{staffStrength}/export-excel',  [FdeStaffStrengthController::class, 'exportExcel'])->name('export-excel');
        });

                // ── App / Dashboard Settings ──────────────────────────────────────
        Route::get( 'app-settings',        [AppSettingsController::class, 'index']  )->name('app-settings.index');
        Route::put( 'app-settings',        [AppSettingsController::class, 'update'] )->name('app-settings.update');

        // ── System Reset ──────────────────────────────────────────────────
        Route::get( 'system-reset',        [SystemResetController::class, 'index']  )->name('system-reset.index');
        Route::post('system-reset/execute',[SystemResetController::class, 'reset']  )->name('system-reset.execute');


    });


    // ════════════════════════════════════════════════════════════════════════
    //  AEO — Read-only
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:aeo'])->prefix('aeo')->name('aeo.')->group(function () {

        Route::get('dashboard', [AeoDashboardController::class, 'index'])->name('dashboard');

        Route::get('reports/dashboard', [ReportDashboardController::class, 'index'])        ->name('reports.dashboard');
        Route::get('reports/sector',    [ReportDashboardController::class, 'sectorReport']) ->name('reports.sector');
        Route::get('reports/vacancy',   [ReportDashboardController::class, 'vacancyReport'])->name('reports.vacancy');
        Route::get('reports/gender',    [ReportDashboardController::class, 'genderReport']) ->name('reports.gender');
        Route::get('reports/oosc',      [ReportDashboardController::class, 'ooscReport'])   ->name('reports.oosc');

        Route::get('export/vacancy', [ExportController::class, 'vacancyReport'])->name('export.vacancy');
        Route::get('export/oosc',    [ExportController::class, 'ooscReport'])   ->name('export.oosc');

        // Monitoring — read-only, scoped to this AEO's sector
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/',             [AeoMonitoringController::class, 'index'])->name('index');
            Route::get('/{monitoring}', [AeoMonitoringController::class, 'show'] )->name('show');
        });

        // Staff Strength Register — read-only, sector-scoped
        Route::prefix('staff-strength')->name('staff-strength.')->group(function () {
            Route::get('/',                [AeoStaffStrengthController::class, 'index'])->name('index');
            Route::get('/{staffStrength}', [AeoStaffStrengthController::class, 'show'] )->name('show');
        });

    }); // end role:aeo


    // ════════════════════════════════════════════════════════════════════════
    //  DIRECTOR / DG / SECRETARY — Read-only, system-wide
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:director'])->prefix('director')->name('director.')->group(function () {

        // Executive Dashboard — dedicated controller with rich analytics
        Route::get('dashboard', [DirectorDashboardController::class, 'index'])->name('dashboard');

        // Reports — ReportDashboardController: sectorIds() returns null for director (no restriction)
        //           renders aeo.reports.* blades (read-only, no FDE action buttons)
        Route::get('reports/dashboard', [ReportDashboardController::class, 'index']        )->name('reports.dashboard');
        Route::get('reports/sector',    [ReportDashboardController::class, 'sectorReport'] )->name('reports.sector');
        Route::get('reports/vacancy',   [ReportDashboardController::class, 'vacancyReport'])->name('reports.vacancy');
        Route::get('reports/gender',    [ReportDashboardController::class, 'genderReport'] )->name('reports.gender');
        Route::get('reports/oosc',      [ReportDashboardController::class, 'ooscReport']   )->name('reports.oosc');
        Route::get('reports/master',    [MasterReportController::class,    'index']        )->name('reports.master');

        // Exports
        Route::get('export/vacancy', [ExportController::class, 'vacancyReport'])->name('export.vacancy');
        Route::get('export/oosc',    [ExportController::class, 'ooscReport']   )->name('export.oosc');
        Route::get('export/master',  [ExportController::class, 'masterReport'] )->name('export.master');

        // Monitoring — read-only, all schools
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/',             [DirectorMonitoringController::class, 'index'])->name('index');
            Route::get('/{monitoring}', [DirectorMonitoringController::class, 'show'] )->name('show');
        });

        // Staff Strength Register — read-only + export, system-wide
        Route::prefix('staff-strength')->name('staff-strength.')->group(function () {
            Route::get('/',                              [DirectorStaffStrengthController::class, 'index']      )->name('index');
            Route::get('/{staffStrength}',               [DirectorStaffStrengthController::class, 'show']       )->name('show');
            Route::get('/{staffStrength}/export-pdf',    [DirectorStaffStrengthController::class, 'exportPdf']  )->name('export-pdf');
            Route::get('/{staffStrength}/export-excel',  [DirectorStaffStrengthController::class, 'exportExcel'])->name('export-excel');
        });

    }); // end role:director

    // ════════════════════════════════════════════════════════════════════════
    //  ANNOUNCEMENTS — FDE Cell only, named announcements.* to match views
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:fde_cell'])
        ->group(function () {
            Route::resource('announcements', AnnouncementController::class);
        });

    // ════════════════════════════════════════════════════════════════════════
    //  ADMIN — FDE Cell only
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:fde_cell'])->prefix('admin')->name('admin.')->group(function () {

        Route::resource('ucs', UnionCouncilController::class)
            ->except(['show'])
            ->parameters(['ucs' => 'unionCouncil']);

        Route::resource('sectors', SectorController::class)
            ->except(['show']);

        Route::resource('institutions', InstitutionController::class);

        Route::resource('users', UserController::class)
            ->except(['show']);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        Route::get( 'import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import', [ImportController::class, 'store'])->name('import.store');

        // Academic Years CRUD
        Route::resource('academic-years', AcademicYearController::class)
            ->except(['show']);
        Route::post('academic-years/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])
            ->name('academic-years.set-active');
    });


    // ════════════════════════════════════════════════════════════════════════
    //  UC CONTROL ROOMS — accessible to all 4 roles (read-only listing)
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:fde_cell|hoi|aeo|director'])
         ->prefix('uc-control-rooms')
         ->name('uc.control-rooms.')
         ->group(function () {

        Route::get('/',           [UcControlRoomController::class, 'index']     )->name('index');
        Route::get('/export-pdf', [UcControlRoomController::class, 'exportPdf'] )->name('export-pdf');
        Route::get('/{ucControlRoom}', [UcControlRoomController::class, 'show'] )->name('show');
    });


    // ════════════════════════════════════════════════════════════════════════
    //  COLLEGES — Model + Ex-FG (all roles; hoi/aeo/director see own scope)
    // ════════════════════════════════════════════════════════════════════════
    Route::middleware(['role:fde_cell|hoi|aeo|director'])
         ->prefix('colleges')
         ->name('fde.colleges.')
         ->group(function () {

        Route::get('model',             [CollegeController::class, 'modelColleges'])->name('model');
        Route::get('ex-fg',             [CollegeController::class, 'exFgColleges'] )->name('ex-fg');
        Route::get('export-pdf/{type}', [CollegeController::class, 'exportPdf']    )->name('export-pdf');
        Route::get('{institution}',     [CollegeController::class, 'profile']       )->name('profile');
    });


});
