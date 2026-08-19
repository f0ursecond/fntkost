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
