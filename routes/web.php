<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// --- SUPER ADMIN ONLY ---
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::resource('users', UserController::class);
     Route::resource('activity', LogActivityController::class);
});

// --- KASIR & SUPER ADMIN ---
Route::middleware(['auth', 'role:Kasir|Super Admin'])->group(function () {
    Route::get('/api/procedures/{id}/price', [TransactionController::class, 'getProcedurePrice']);
    Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'markAsPaid'])->name('transactions.pay');
    Route::get('/transactions/{transaction}/print', [TransactionController::class, 'printPdf'])->name('transactions.print');

    // Resource routes for transactions & payments
    Route::resource('transactions', TransactionController::class);
    // Route::resource('payment', PaymentController::class);
});

// --- MARKETING & SUPER ADMIN ---
Route::middleware(['auth', 'role:Marketing|Super Admin'])->group(function () {
    Route::resource('voucher', VoucherController::class);
    // Route::resource('report', ReportController::class);
});

// --- ALL AUTHENTICATED USERS ---

Route::middleware('auth')->
group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
