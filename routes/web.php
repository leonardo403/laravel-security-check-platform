<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlansController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/metrics', [DashboardController::class, 'metrics'])->name('metrics');

    Route::resource('scans', ScanController::class);
    Route::get('/scans/{scan}/progress', [ScanController::class, 'progress'])->name('scans.progress');

    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}', [PlansController::class, 'show'])->name('plans.show');
    Route::post('/plans/{plan}/subscribe', [PlansController::class, 'subscribe'])->name('plans.subscribe');
});
