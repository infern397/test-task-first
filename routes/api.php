<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkHourController;
use Illuminate\Support\Facades\Route;

Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/work-hours', [WorkHourController::class, 'store'])->name('work-hours.store');
Route::get('/unpaid-salaries', [WorkHourController::class, 'unpaidSalaries'])->name('work-hours.unpaid-salaries');
Route::post('/pay-salaries', [WorkHourController::class, 'paySalaries'])->name('work-hours.pay-salaries');
