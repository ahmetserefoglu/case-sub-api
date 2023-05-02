<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [DeviceController::class, 'register']);
Route::post('/check', [SubscriptionController::class, 'check']);
Route::post('/verify-purchase', [SubscriptionController::class, 'verifyPurchase']);
Route::post('/subscription-status', [SubscriptionController::class, 'getSubscriptionStatus']);
Route::get('/purchase-history', [SubscriptionController::class, 'history']);



