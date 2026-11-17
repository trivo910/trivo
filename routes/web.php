<?php

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\EnquiryController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ResolvePublicContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');
Route::get('/services', [HomeController::class, 'services'])->name('services');
Route::get('/meet-the-team', [HomeController::class, 'meetTheTeam'])->name('team');
Route::get('/awards', [HomeController::class, 'awards'])->name('awards');
Route::get('/career', [HomeController::class, 'career'])->name('career');
Route::get('/portfolio', [HomeController::class, 'portfolio'])->name('portfolio');
Route::get('/portfolio/{slug}', [HomeController::class, 'portfolioItem'])->name('portfolio.item');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:leads')->name('enquiries.store');
Route::get('/thank-you', [HomeController::class, 'thankYou'])->name('thank-you');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.post');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [HomeController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/return-refund-policy', [HomeController::class, 'refundPolicy'])->name('return-refund-policy');

Route::fallback(ResolvePublicContentController::class);
