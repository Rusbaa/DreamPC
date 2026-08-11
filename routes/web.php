<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ChatController;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index']);
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

// Chat Interface Routes
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

// Build Cost Calculation Route
Route::match(['get', 'post'], '/api/build/calculate-cost', [BuildController::class, 'calculateCost']);
Route::match(['get', 'post'], '/build/summary', [BuildController::class, 'showSummary'])->name('build.summary');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin Protected Routes
Route::middleware(['auth', CheckAdmin::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the Admin Dashboard']);
    });
    Route::resource('products', ProductController::class);
});
