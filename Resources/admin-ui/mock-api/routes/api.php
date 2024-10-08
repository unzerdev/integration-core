<?php

use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\StoresController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/stores', [StoresController::class, 'stores']);
Route::get('/paymentMethods', [PaymentMethodsController::class, 'getPaymentMethods']);
Route::get('/checkoutSettings/icons', [PaymentMethodsController::class, 'icons']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
