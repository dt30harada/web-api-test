<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::factory()->count(10)->create();

        $now = now();
        $details = $orders->map(function (Order $order) use ($now) {
            return OrderDetail::factory()->count(mt_rand(1, 3))->make([
                'order_id' => $order->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray();
        })->flatten(1);
        $details->chunk(1000)->each(fn ($chunk) => OrderDetail::insert($chunk->toArray()));
    }
}
