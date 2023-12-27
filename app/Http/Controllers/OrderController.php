<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Order\AlreadyOrderedException;
use App\Mail\Order\OrderPlaced;
use App\Models\Ebook\Ebook;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class OrderController extends Controller
{
    /**
     * 注文処理
     *
     * @return void
     */
    public function placeOrder(Request $request)
    {
        // バリデーション
        $request->validate([
            'ebookIds' => 'required|array|exists:ebooks,id',
        ]);

        /** @var int[] $ebookIds */
        $ebookIds = $request->input('ebookIds');
        /** @var User $user */
        $user = $request->user();

        $order = DB::transaction(function () use ($user, $ebookIds) {

            // ユーザーが過去に注文した電子書籍は注文できない
            $isAlreadyOrdered = $user
                ->orders()
                ->whereHas('orderDetails', function (Builder $query) use ($ebookIds) {
                    $query->whereIn('ebook_id', $ebookIds);
                })
                ->lockForUpdate()
                ->exists();
            if ($isAlreadyOrdered) {
                throw new AlreadyOrderedException;
            }

            // 注文詳細を作成
            $totalPrice = 0;
            $orderDetails = [];
            $ebooks = Ebook::find($ebookIds);
            foreach ($ebooks as $ebook) {
                $orderDetail = new OrderDetail;
                $orderDetail->fill([
                    'ebook_id' => $ebook->id,
                    'ebook_title' => $ebook->title,
                    'price' => $ebook->price,
                ]);
                $orderDetails[] = $orderDetail;
                $totalPrice += $orderDetail->price;
            }

            // 注文を保存
            $order = new Order;
            $order->total_price = $totalPrice;
            $user->orders()->save($order);

            // 注文詳細を保存
            $order->orderDetails()->saveMany($orderDetails);

            // 決済
            $this->executePayment($order);

            return $order;
        });

        $order->refresh();

        // 注文完了メールを送信
        Mail::to($request->user())->send(new OrderPlaced($order));

        return response()->json([
            'order_id' => $order->id,
        ]);
    }

    /**
     * 決済処理
     *
     * @throws \Exception 決済処理に失敗した場合
     *
     * @todo 決済サービス選定後に実装する
     */
    private function executePayment(Order $order): void
    {
        // 外部決済サービスのAPIを実行する処理を記述する
    }
}
