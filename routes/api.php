<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ClientMsaController;
use App\Http\Controllers\Api\ClientPoController;
use App\Http\Controllers\Api\ClientTermsController;
use App\Http\Controllers\Api\VendorPoController;
use App\Http\Controllers\Api\VendorKycController;
use App\Http\Controllers\Api\VendorRegistrationController;
use App\Http\Controllers\Api\ContactInquiryController;
use App\Http\Controllers\Api\ClientRegistrationController;
use App\Http\Middleware\ApiAuthMiddleware;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/handle-client-msa', [ClientMsaController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-client-po', [ClientPoController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-client-terms', [ClientTermsController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-vendor-po', [VendorPoController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-vendor-kyc', [VendorKycController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-vendor-registration', [VendorRegistrationController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-contact-inquiry', [ContactInquiryController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);

Route::post('/handle-client-registration', [ClientRegistrationController::class, 'handle'])
    ->middleware(ApiAuthMiddleware::class);
