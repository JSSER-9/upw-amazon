<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/get_rates', [\App\Http\Controllers\APIController::class, 'get_rates']);

Route::get('/purchase_label', [\App\Http\Controllers\APIController::class, 'purchase_label']);

Route::get('/get_shipment/{shipment_id}', [\App\Http\Controllers\APIController::class, 'get_shipment']);

Route::post('/purchase_labels/{shipment_id}', [\App\Http\Controllers\APIController::class, 'purchase_label']);

Route::post('/cancel_shipment/{shipment_id}', [\App\Http\Controllers\APIController::class, 'cancel_shipment']);

Route::get('/get_tracking_info/{tracking_id}', [\App\Http\Controllers\APIController::class, 'get_tracking_info']);