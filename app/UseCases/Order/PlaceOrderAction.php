<?php

declare(strict_types=1);

namespace App\UseCases\Order;

use App\Exceptions\Order\AlreadyOrderedException;
use App\Mail\Order\OrderPlaced;
use App\Models\Ebook\Ebook;
use App\Models\Order\Discounter;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\User;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * 注文を処理する
 */
final class PlaceOrderAction
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
    ) {
    }

    public function __invoke(array $ebookIds, User $user): Order
    {
        $order = DB::transaction(function () use ($user, $ebookIds) {

            if ($user->checkIfAlreadyOrdered($ebookIds)) {
                throw new AlreadyOrderedException;
            }

            $ebooks = Ebook::find($ebookIds)->all();
            [$order, $orderDetails] = $this->makeOrder($ebooks);

            $this->saveOrder($user, $order, $orderDetails);

            $this->executePayment($order);

            return $order;
        });

        $this->sendOrderPlacedMail($user, $order);

        return $order;
    }

    /**
     * 注文モデルを生成する
     *
     * @param  Ebook[]  $ebooks
     * @return array{0: Order, 1: OrderDetail[]}
     */
    private function makeOrder(array $ebooks): array
    {
        $order = new Order;
        $orderDetails = [];

        foreach ($ebooks as $ebook) {
            $orderDetail = new OrderDetail;
            $orderDetail->fill([
                'ebook_id' => $ebook->id,
                'ebook_title' => $ebook->title,
                'price' => $ebook->price,
            ]);
            $orderDetails[] = $orderDetail;
            $order->sub_total += $orderDetail->price;
        }

        $discounter = new Discounter($order, Carbon::now());
        $order->discount = $discounter->calculate();
        $order->total = $order->sub_total - $order->discount;

        return [$order, $orderDetails];
    }

    /**
     * 注文を保存する
     */
    private function saveOrder(User $user, Order $order, array $orderDetails): Order
    {
        $user->orders()->save($order);
        $order->orderDetails()->saveMany($orderDetails);

        return $order->refresh();
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
        $data = [];
        $this->paymentService->execute($data);
    }

    /**
     * 注文完了メールを送信する
     */
    private function sendOrderPlacedMail(User $user, Order $order): void
    {
        Mail::to($user)->send(new OrderPlaced($order));
    }
}
