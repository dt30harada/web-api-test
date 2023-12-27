<?php

use App\Http\Controllers\Order\OrderV1Controller;
use App\Http\Controllers\Order\OrderV2Controller;
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

Route::middleware('auth:sanctum')->group(function () {
    // ログインユーザー情報
    Route::get('user', fn (Request $request) => $request->user());

    // 注文処理 リファクタリング前
    Route::post('v1/orders', [OrderV1Controller::class, 'placeOrder']);
    // 注文処理 リファクタリング後
    Route::post('v2/orders', [OrderV2Controller::class, 'placeOrder']);
});
