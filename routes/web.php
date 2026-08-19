<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('tenants.index');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::resource('tenants', TenantController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('tenants/{tenant}/pay', [TenantController::class, 'pay'])->name('tenants.pay');
});

Route::get('/run-scheduler', function (Illuminate\Http\Request $request) {
    $authHeader = $request->header('Authorization');
    $cronSecret = env('CRON_SECRET');

    // Pastikan request datang dari Vercel (memiliki CRON_SECRET yang cocok)
    if ($cronSecret && $authHeader !== "Bearer " . $cronSecret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Menjalankan scheduler Laravel
    Illuminate\Support\Facades\Artisan::call('schedule:run');

    return response()->json([
        'message' => 'Scheduler ran successfully',
        'output' => Illuminate\Support\Facades\Artisan::output()
    ]);
});

