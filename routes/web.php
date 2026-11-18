<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminFineController;
use App\Http\Controllers\AdminMonthlyReportController;
use App\Http\Controllers\AdminReturnBookController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BookCommunityController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog');
Route::get('/panduan', GuideController::class)->name('guide');

Auth::routes(['reset' => false]);
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:20,1')->name('password.email');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.login.store');

Route::get('/home', [HomeController::class, 'index'])->middleware(['auth', 'account.active'])->name('home');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::post('/books/{book}/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::patch('/loans/{loan}/return', [LoanController::class, 'return'])->name('loans.return');
    Route::get('/loans', [LoanController::class, 'mine'])->name('loans.mine');
    Route::get('/loans/status', [LoanController::class, 'mineStatus'])->name('loans.status');
    Route::post('/books/{book}/favorite', [BookCommunityController::class, 'toggleFavorite'])->name('books.favorite');
    Route::post('/books/{book}/reviews', [BookCommunityController::class, 'storeReview'])->name('books.reviews.store');
    Route::put('/reading-challenge', [BookCommunityController::class, 'updateChallenge'])->name('reading-challenge.update');
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/password-reset-code', [AdminUserController::class, 'issuePasswordResetCode'])->middleware('throttle:10,1')->name('users.password-reset-code');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/', [AdminBookController::class, 'create'])->name('dashboard');
        Route::get('/books/create', [AdminBookController::class, 'create'])->name('books.create');
        Route::post('/books', [AdminBookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [AdminBookController::class, 'edit'])->name('books.edit');
        Route::patch('/books/{book}', [AdminBookController::class, 'update'])->name('books.update');
        Route::get('/loans', [LoanController::class, 'adminIndex'])->name('loans.index');
        Route::get('/loans/scan', [LoanController::class, 'scanPage'])->name('loans.scan');
        Route::post('/loans/confirm-handoff', [LoanController::class, 'confirmHandoff'])->middleware('throttle:60,1')->name('loans.confirm-handoff');
        Route::patch('/loans/{loan}/status', [LoanController::class, 'updateStatus'])->name('loans.status');
        Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
        Route::post('/loans/{loan}/return', [AdminReturnBookController::class, 'store'])->name('loans.return.process');
        Route::patch('/fines/{fine}/paid', [AdminFineController::class, 'markPaid'])->name('fines.paid');
        Route::get('/reports/monthly', [AdminMonthlyReportController::class, 'index'])->name('reports.monthly');
        Route::get('/reports/monthly/pdf', [AdminMonthlyReportController::class, 'download'])->name('reports.monthly.pdf');
    });
});
