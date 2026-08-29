<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\SetLocale;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $plans = Schema::hasTable('subscription_plans')
        ? SubscriptionPlan::where('is_active', true)->orderBy('price')->get()
        : collect();

    return view('welcome', compact('plans'));
});

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

    session(['locale' => $locale]);
    cookie()->queue('locale', $locale, 60 * 24 * 365);

    return redirect()->back();
})->name('locale.switch');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])->withoutMiddleware('csrf');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/metrics', [DashboardController::class, 'metrics'])->name('metrics');

    Route::resource('scans', ScanController::class);
    Route::get('/scans/{scan}/progress', [ScanController::class, 'progress'])->name('scans.progress');

    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}', [PlansController::class, 'show'])->name('plans.show');
    Route::get('/plans/{plan}/checkout', [PlansController::class, 'checkout'])->name('plans.checkout');
    Route::get('/subscription/confirm', [SubscriptionController::class, 'confirm'])->name('subscription.confirm');

    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/plans', [AdminController::class, 'plans'])->name('plans');
        Route::post('/plans/{plan}', [AdminController::class, 'updatePlan'])->name('plans.update');
    });
});
