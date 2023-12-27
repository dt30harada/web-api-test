<?php

declare(strict_types=1);

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Models\User;
use App\UseCases\Order\PlaceOrderAction;

/**
 * 電子書籍の注文を扱うコントローラ リファクタリング後
 */
final class OrderV2Controller extends Controller
{
    /**
     * 注文処理
     *
     * @return void
     */
    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action)
    {
        /** @var int[] $ebookIds */
        $ebookIds = array_unique($request->input('ebookIds'));
        /** @var User $user */
        $user = $request->user();

        $order = $action($ebookIds, $user);

        return response()->json([
            'orderId' => $order->id,
        ]);
    }
}
