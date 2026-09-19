<?php

use Illuminate\Support\Facades\Route;
use App\Models\Book;

Route::get('/', function () {
    return view('welcome', [
        'books' => Book::latest()->take(6)->get(),
    ]);
});

Auth::routes();

Route::get('/admin/login', [App\Http\Controllers\AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\AdminAuthController::class, 'store'])->name('admin.login.store');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::post('/books/{book}/loans', [App\Http\Controllers\LoanController::class, 'store'])->name('loans.store');
    Route::patch('/loans/{loan}/return', [App\Http\Controllers\LoanController::class, 'return'])->name('loans.return');
    Route::get('/loans', [App\Http\Controllers\LoanController::class, 'mine'])->name('loans.mine');
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminBookController::class, 'create'])->name('dashboard');
        Route::get('/books/create', [App\Http\Controllers\AdminBookController::class, 'create'])->name('books.create');
        Route::post('/books', [App\Http\Controllers\AdminBookController::class, 'store'])->name('books.store');
        Route::get('/loans', [App\Http\Controllers\LoanController::class, 'adminIndex'])->name('loans.index');
        Route::patch('/loans/{loan}/status', [App\Http\Controllers\LoanController::class, 'updateStatus'])->name('loans.status');
        Route::post('/loans/{loan}/return', [App\Http\Controllers\AdminReturnBookController::class, 'store'])->name('loans.return.process');
    });
});
