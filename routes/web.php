<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SetupController;
use App\Http\Controllers\CollectController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\PublicWallController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WallFeedController;
use Illuminate\Support\Facades\Route;

// First run: create the admin account.
Route::get('setup', [SetupController::class, 'show'])->name('setup');
Route::post('setup', [SetupController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/walls');
    Route::resource('walls', WallController::class);
    Route::get('walls/{wall}/embed', EmbedController::class)->name('walls.embed');

    Route::post('walls/{wall}/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::get('testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
    Route::put('testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
    Route::patch('testimonials/{testimonial}/status', [TestimonialController::class, 'status'])->name('testimonials.status');
    Route::patch('testimonials/{testimonial}/feature', [TestimonialController::class, 'feature'])->name('testimonials.feature');
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
});

// Public pages: the form customers fill in, and the shareable wall.
Route::get('c/{wall}', [CollectController::class, 'show'])->name('collect.show');
Route::post('c/{wall}', [CollectController::class, 'store'])->middleware('throttle:collect')->name('collect.store');
Route::get('c/{wall}/thanks', [CollectController::class, 'thanks'])->name('collect.thanks');
Route::get('w/{wall}', PublicWallController::class)->name('wall.show');

// Read-only JSON for the embed widget. CORS is open because it's meant to be used on other sites.
Route::get('api/walls/{wall}', WallFeedController::class)->name('api.wall');
