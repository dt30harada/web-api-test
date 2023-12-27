<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Order;

use App\Models\Order\Discounter;
use App\Models\Order\Order;
use DateTime;
use PHPUnit\Framework\TestCase;

final class DiscounterTest extends TestCase
{
    /**
     * @dataProvider data_5のつく日は小計から5パーセント割引する_5のつかない日は割引しない
     */
    public function test_5のつく日は小計から5パーセント割引する_5のつかない日は割引しない(string $date, int $subTotal, int $discount): void
    {
        // arrange
        $order = new Order();
        $order->sub_total = $subTotal;

        // act
        $sut = new Discounter($order, new DateTime($date));

        // assert
        $this->assertSame($discount, $sut->calculate());
    }

    public static function data_5のつく日は小計から5パーセント割引する_5のつかない日は割引しない(): array
    {
        return [
            '2023-12-04_割引額0円' => ['2023-12-04', 100, 0],
            '2023-12-05_割引額0円' => ['2023-12-05', 19, 0],
            '2023-12-05_割引額1円以上' => ['2023-12-05', 20, 1],
            '2023-12-06_割引額0円' => ['2023-12-06', 100, 0],
            '2023-12-14_割引額0円' => ['2023-12-14', 100, 0],
            '2023-12-15_割引額1円以上' => ['2023-12-15', 100, 5],
            '2023-12-16_割引額0円' => ['2023-12-16', 100, 0],
            '2023-12-24_割引額0円' => ['2023-12-24', 100, 0],
            '2023-12-25_割引額1円以上' => ['2023-12-25', 1000, 50],
            '2023-12-26_割引額0円' => ['2023-12-26', 100, 0],
        ];
    }
}
