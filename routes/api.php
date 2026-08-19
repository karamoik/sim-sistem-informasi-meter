<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaptchaController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GarduIndukController;
use App\Http\Controllers\Api\KwhMeterController;
use App\Http\Controllers\Api\JenisKwhMeterController;
use App\Http\Controllers\Api\TitikUkurController;
use App\Http\Controllers\Api\KonfigurasiMeterController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\UserSettingController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\EditingController;
use App\Http\Controllers\Api\LoadProfileController;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Working'
    ]);
});

Route::get('/captcha', [CaptchaController::class, 'generate']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:login');

    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
        ->middleware('throttle:login');

    Route::middleware(['auth:api', 'throttle:api'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::prefix('2fa')->group(function () {
            Route::get('/status', [TwoFactorController::class, 'status']);
            Route::post('/setup', [TwoFactorController::class, 'setup']);
            Route::post('/enable', [TwoFactorController::class, 'enable']);
            Route::post('/disable', [TwoFactorController::class, 'disable']);
        });
    });
});

Route::middleware(['auth:api', 'throttle:api', 'check.session', 'unit.scope'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Load profile monitoring
    Route::get('/monitoring/load-profiles/months', [LoadProfileController::class, 'months']);
    Route::get('/monitoring/load-profiles/daily', [LoadProfileController::class, 'daily']);
    Route::get('/monitoring/load-profiles/weekly', [LoadProfileController::class, 'weekly']);
    Route::get('/monitoring/load-profiles/records', [LoadProfileController::class, 'records']);
    Route::get('/monitoring/load-profiles/availability', [LoadProfileController::class, 'availability']);
    Route::get('/monitoring/load-profiles/availability-detail', [LoadProfileController::class, 'availabilityDetail']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    // Session management
    Route::get('/user/sessions', [SessionController::class, 'index']);
    Route::get('/user/sessions/current', [SessionController::class, 'current']);
    Route::delete('/user/sessions/{id}', [SessionController::class, 'destroy']);
    Route::delete('/user/sessions', [SessionController::class, 'destroyAll']);
    Route::post('/user/sessions/touch', [SessionController::class, 'touch']);

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/types', [ActivityLogController::class, 'types']);
    Route::delete('/activity-logs', [ActivityLogController::class, 'clear']);

    // User settings
    Route::get('/user/settings', [UserSettingController::class, 'index']);
    Route::put('/user/settings', [UserSettingController::class, 'update']);
    Route::get('/user/settings/notifications', [UserSettingController::class, 'getNotificationPrefs']);
    Route::put('/user/settings/notifications', [UserSettingController::class, 'updateNotificationPrefs']);
    Route::get('/user/settings/appearance', [UserSettingController::class, 'getAppearance']);
    Route::put('/user/settings/appearance', [UserSettingController::class, 'updateAppearance']);

    // Export
    Route::get('/export/meters', [ExportController::class, 'meters']);
    Route::get('/export/dashboard', [ExportController::class, 'dashboardStats']);

    // Editing presence (concurrent editing detection)
    Route::post('/editing/start', [EditingController::class, 'start']);
    Route::post('/editing/stop', [EditingController::class, 'stop']);
    Route::post('/editing/heartbeat', [EditingController::class, 'heartbeat']);
    Route::get('/editing/status', [EditingController::class, 'status']);
    Route::post('/editing/field-change', [EditingController::class, 'fieldChange']);
    Route::post('/editing/field-editing', [EditingController::class, 'fieldEditing']);

    // All roles can read
    Route::get('/gardu-induks/all', [GarduIndukController::class, 'index']);
    Route::get('/kwh-meters/all', [KwhMeterController::class, 'index']);
    Route::get('/jenis-kwh-meters/all', [JenisKwhMeterController::class, 'index']);
    Route::get('/titik-ukurs/all', [TitikUkurController::class, 'index']);
    Route::get('/konfigurasi-meters/all', [KonfigurasiMeterController::class, 'index']);

    // Admin only: user management
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class)->except(['create', 'edit']);
        Route::post('/users/{user}/assign-units', [UserController::class, 'assignUnits']);
        Route::get('/available-units', [UserController::class, 'availableUnits']);
    });

    // Admin + Operator: CRUD data
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/gardu-induks/regions', [GarduIndukController::class, 'regions']);
        Route::get('/gardu-induks/trashed', [GarduIndukController::class, 'trashed']);
        Route::post('/gardu-induks/{id}/restore', [GarduIndukController::class, 'restore']);
        Route::delete('/gardu-induks/{id}/force-delete', [GarduIndukController::class, 'forceDelete']);
        Route::apiResource('gardu-induks', GarduIndukController::class)->except(['create', 'edit']);

        Route::get('/kwh-meters/trashed', [KwhMeterController::class, 'trashed']);
        Route::post('/kwh-meters/{id}/restore', [KwhMeterController::class, 'restore']);
        Route::delete('/kwh-meters/{id}/force-delete', [KwhMeterController::class, 'forceDelete']);
        Route::apiResource('kwh-meters', KwhMeterController::class)->except(['create', 'edit']);

        Route::get('/jenis-kwh-meters/trashed', [JenisKwhMeterController::class, 'trashed']);
        Route::post('/jenis-kwh-meters/{id}/restore', [JenisKwhMeterController::class, 'restore']);
        Route::delete('/jenis-kwh-meters/{id}/force-delete', [JenisKwhMeterController::class, 'forceDelete']);
        Route::apiResource('jenis-kwh-meters', JenisKwhMeterController::class)->except(['create', 'edit']);

        Route::get('/titik-ukurs/trashed', [TitikUkurController::class, 'trashed']);
        Route::post('/titik-ukurs/{id}/restore', [TitikUkurController::class, 'restore']);
        Route::delete('/titik-ukurs/{id}/force-delete', [TitikUkurController::class, 'forceDelete']);
        Route::apiResource('titik-ukurs', TitikUkurController::class)->except(['create', 'edit']);

        Route::get('/konfigurasi-meters/trashed', [KonfigurasiMeterController::class, 'trashed']);
        Route::post('/konfigurasi-meters/{id}/restore', [KonfigurasiMeterController::class, 'restore']);
        Route::delete('/konfigurasi-meters/{id}/force-delete', [KonfigurasiMeterController::class, 'forceDelete']);
        Route::apiResource('konfigurasi-meters', KonfigurasiMeterController::class)->except(['create', 'edit']);

        // Export per module
        Route::get('/export/gardu-induks', [ExportController::class, 'garduInduks']);
        Route::get('/export/titik-ukurs', [ExportController::class, 'titikUkurs']);
        Route::get('/export/jenis-kwh-meters', [ExportController::class, 'jenisKwhMeters']);
        Route::get('/export/konfigurasi-meters', [ExportController::class, 'konfigurasiMeters']);

        // Import
        Route::post('/import', [ExportController::class, 'import']);
        Route::get('/import/template', [ExportController::class, 'template']);
    });

    Route::get('/konfigurasi-meters/{konfigurasiMeter}/document', [KonfigurasiMeterController::class, 'downloadDocument'])->name('konfigurasi-meters.document');

});
