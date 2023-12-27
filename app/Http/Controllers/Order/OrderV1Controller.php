<?php

declare(strict_types=1);

namespace App\Http\Controllers\Order;

use App\Exceptions\Order\AlreadyOrderedException;
use App\Http\Controllers\Controller;
use App\Mail\Order\OrderPlaced;
use App\Models\Ebook\Ebook;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * 電子書籍の注文を扱うコントローラ リファクタリング前
 */
final class OrderV1Controller extends Controller
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
            'ebookIds' => 'required|array|max:100|exists:ebooks,id',
        ]);

        /** @var int[] $ebookIds */
        $ebookIds = array_unique($request->input('ebookIds'));
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
            $subTotal = 0;
            $discount = 0;
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
                $subTotal += $orderDetail->price;
            }

            // 5がつく日は5%引
            if (str_contains((string) Carbon::now()->day, '5')) {
                $discount = (int) ($subTotal * 0.05);
            }

            // 注文を保存
            $order = new Order;
            $order->fill([
                'sub_total' => $subTotal,
                'discount' => $discount,
                'total' => $subTotal - $discount,
            ]);
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
            'orderId' => $order->id,
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
