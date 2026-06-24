// routes/web.php
<?php
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlansController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/metrics', [DashboardController::class, 'metrics'])->name('metrics');

    Route::resource('scans', ScanController::class);

    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}', [PlansController::class, 'show'])->name('plans.show');
    Route::post('/plans/{plan}/subscribe', [PlansController::class, 'subscribe'])->name('plans.subscribe');
});

require __DIR__.'/auth.php';
