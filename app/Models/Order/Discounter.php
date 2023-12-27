<?php

declare(strict_types=1);

namespace App\Models\Order;

use DateTimeInterface;

final class Discounter
{
    public function __construct(
        private readonly Order $order,
        private readonly DateTimeInterface $now,
    ) {
    }

    /**
     * 割引額を計算する
     *
     * @note 今日が5のつく日なら5%割引する
     */
    public function calculate(): int
    {
        $discount = 0;
        if ($this->isFiveDay()) {
            $discount = (int) bcmul((string) $this->order->sub_total, '0.05', 0);
        }

        return $discount;
    }

    /**
     * 今日が5のつく日か判定する
     */
    private function isFiveDay(): bool
    {
        return str_contains($this->now->format('d'), '5');
    }
}
