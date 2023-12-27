<?php

declare(strict_types=1);

namespace App\Services\Payment;

interface PaymentServiceInterface
{
    /**
     * 決済を実行する
     */
    public function execute(array $data): void;
}
