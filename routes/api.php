<?php

use App\Http\Controllers\OrderController;
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

    Route::prefix('orders')->group(function () {
        // 注文処理
        Route::post('', [OrderController::class, 'placeOrder']);
    });
});
