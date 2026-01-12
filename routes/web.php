<?php

use App\Http\Controllers\DirectLinkController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ShareableLinksController;
use Common\Core\Controllers\HomeController;
use Common\Pages\CustomPageController;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Route;
use Faker\Generator;

//FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
Route::get('/', LandingPageController::class);
Route::get('drive/s/{hash}', [ShareableLinksController::class, 'show']);
Route::get('d/{linkHash}/{fileHash}.{extension}', [
    DirectLinkController::class,
    'show',
]);

Route::get('contact', [HomeController::class, 'render']);
Route::get('pages/{slugOrId}', [CustomPageController::class, 'show']);
Route::get('login', [HomeController::class, 'render'])->name('login');
Route::get('register', [HomeController::class, 'render'])->name('register');
Route::get('forgot-password', [HomeController::class, 'render']);
Route::get('pricing', '\Common\Billing\PricingPageController');

Route::get('gigi',function (){
    dd(settings('uploading.backends'));
});

//CATCH ALL ROUTES AND REDIRECT TO HOME
Route::fallback([HomeController::class, 'render']);
