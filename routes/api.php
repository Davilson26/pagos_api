<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PaymentController;

Route::post('/payments/process', [PaymentController::class, 'processPayment']);
