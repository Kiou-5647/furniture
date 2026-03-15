<?php

use App\Http\Controllers\Employee\EmployeeDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user_type:employee'])->group(function () {
    Route::get('/nhan-vien/dashboard', [EmployeeDashboardController::class, 'index'])
        ->name('employee.dashboard');
});
