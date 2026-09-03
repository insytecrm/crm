<?php

use App\Http\Controllers\Landing\LandingSubmissionController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('landing')->name('landing.')->group(function () {
    Route::post('/demo', [LandingSubmissionController::class, 'storeDemo'])->name('demo.store');
    Route::post('/trial', [LandingSubmissionController::class, 'storeTrial'])->name('trial.store');
});

Route::view('/privacy', 'landing.privacy')->name('privacy');
Route::view('/terms', 'landing.terms')->name('terms');
Route::view('/refund', 'landing.refund')->name('refund');

Route::prefix('platform')->group(function () {
    require __DIR__.'/auth.php';

    Route::get('/dashboard', function () {
        return redirect()->route('tenants.index');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::resource('tenants', TenantController::class);
    });
});
